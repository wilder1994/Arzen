<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponPhotoStaging;
use App\Services\FieldRevistaAssignmentService;
use App\Services\RevistaArmasScopeService;
use App\Support\RevistaWeaponPhotoSlots;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RevistaArmasController extends Controller
{
    private const INDEX_PER_PAGE = 50;

    private const ASSIGN_SEARCH_LIMIT = 60;

    public function __construct(
        private readonly RevistaArmasScopeService $scopeService,
        private readonly FieldRevistaAssignmentService $assignmentService,
    ) {
        $this->middleware(['auth', 'revista.staff']);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $weapons = $this->scopeService
            ->weaponsQueryForStaff($user, [
                'activeClientAssignment.client',
                'activePostAssignment.post',
                'activeWorkerAssignment.worker',
                'photos',
            ])
            ->paginate(self::INDEX_PER_PAGE)
            ->withQueryString();

        $weaponIds = $weapons->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $inReview = array_fill_keys(
            WeaponPhotoStaging::query()
                ->whereIn('weapon_id', $weaponIds)
                ->whereIn('description', RevistaWeaponPhotoSlots::keys())
                ->distinct()
                ->pluck('weapon_id')
                ->map(fn ($id) => (int) $id)
                ->all(),
            true
        );

        $rows = $weapons->getCollection()->map(function (Weapon $weapon) use ($inReview) {
            $official = $weapon->photos
                ->whereIn('description', RevistaWeaponPhotoSlots::keys())
                ->pluck('description')
                ->unique()
                ->count();

            if (isset($inReview[$weapon->id])) {
                $status = __('en revisión');
            } elseif ($official >= RevistaWeaponPhotoSlots::requiredCount()) {
                $status = __('al día');
            } else {
                $status = __(':done de :total', [
                    'done' => $official,
                    'total' => RevistaWeaponPhotoSlots::requiredCount(),
                ]);
            }

            return ['weapon' => $weapon, 'status' => $status];
        });

        return view('revista-armas.index', [
            'weapons' => $weapons,
            'rows' => $rows,
            'fieldUsers' => $this->scopeService->fieldUsersQueryForStaff($user)->get(),
            'assignmentNotice' => session('revista_assignment_notice'),
        ]);
    }

    public function searchAssignableWeapons(Request $request): JsonResponse
    {
        $term = trim($request->string('q')->toString());
        $limit = min(100, max(1, $request->integer('limit') ?: self::ASSIGN_SEARCH_LIMIT));

        $query = $this->scopeService->weaponsQueryForStaff($request->user(), [
            'activeClientAssignment.client',
            'activePostAssignment.post',
            'activeWorkerAssignment.worker',
        ]);

        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function ($inner) use ($like) {
                $inner
                    ->where('serial_number', 'like', $like)
                    ->orWhere('internal_code', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('caliber', 'like', $like)
                    ->orWhere('weapon_type', 'like', $like)
                    ->orWhereHas('activeClientAssignment.client', function ($clientQuery) use ($like) {
                        $clientQuery->where('name', 'like', $like);
                    })
                    ->orWhereHas('activePostAssignment.post', function ($postQuery) use ($like) {
                        $postQuery->where('name', 'like', $like);
                    })
                    ->orWhereHas('activeWorkerAssignment.worker', function ($workerQuery) use ($like) {
                        $workerQuery->where('name', 'like', $like);
                    });
            });
        }

        /** @var Collection<int, Weapon> $weapons */
        $weapons = $query->limit($limit)->get();

        return response()->json([
            'items' => $weapons->map(function (Weapon $weapon) {
                $clientName = $weapon->activeClientAssignment?->client?->name;

                $assignment = [];
                if ($weapon->activePostAssignment?->post) {
                    $assignment[] = __('Puesto').': '.$weapon->activePostAssignment->post->name;
                }
                if ($weapon->activeWorkerAssignment?->worker) {
                    $assignment[] = __('Trabajador').': '.$weapon->activeWorkerAssignment->worker->name;
                }

                return [
                    'id' => $weapon->id,
                    'serial_number' => $weapon->serial_number,
                    'weapon_type' => $weapon->weapon_type,
                    'brand' => $weapon->brand,
                    'client_name' => $clientName ?: '—',
                    'assignment' => $assignment,
                ];
            })->values()->all(),
        ]);
    }

    public function assign(Request $request)
    {
        $data = $request->validate([
            'field_user_id' => ['required', 'integer', 'exists:users,id'],
            'weapon_ids' => ['required', 'array', 'min:1'],
            'weapon_ids.*' => ['integer'],
        ]);

        $fieldUser = User::query()->findOrFail($data['field_user_id']);
        $result = $this->assignmentService->assign($request->user(), $fieldUser, $data['weapon_ids']);

        $redirect = back()->with('status', __('Armas enviadas a campo.'));

        if ($result['notice']) {
            $redirect->with('revista_assignment_notice', [
                'field_name' => $result['field_name'],
                'owner_name' => $result['owner_name'],
            ]);
        }

        return $redirect;
    }

    public function photos(Request $request, Weapon $weapon): JsonResponse
    {
        if (! $this->scopeService->canStaffManageWeapon($request->user(), $weapon)) {
            abort(403);
        }

        $weapon->load('photos.file');
        $official = $weapon->photos->keyBy('description');

        $staging = WeaponPhotoStaging::query()
            ->with(['file', 'fieldUser:id,name'])
            ->where('weapon_id', $weapon->id)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->get()
            ->groupBy('user_id');

        return response()->json([
            'weapon' => [
                'id' => $weapon->id,
                'serial_number' => $weapon->serial_number,
            ],
            'slots' => collect(RevistaWeaponPhotoSlots::descriptions())->map(function (string $label, string $description) use ($official) {
                $photo = $official->get($description);

                return [
                    'description' => $description,
                    'label' => $label,
                    'url' => $photo?->file ? Storage::disk($photo->file->disk)->url($photo->file->path) : null,
                ];
            })->values(),
            'reviews' => $staging->map(function ($rows, $userId) {
                $fieldUser = $rows->first()?->fieldUser;
                $byDescription = $rows->keyBy('description');

                return [
                    'field_user_id' => (int) $userId,
                    'name' => $fieldUser?->name ?? '—',
                    'complete' => $rows->pluck('description')->unique()->count() >= RevistaWeaponPhotoSlots::requiredCount(),
                    'slots' => collect(RevistaWeaponPhotoSlots::descriptions())->map(function (string $label, string $description) use ($byDescription) {
                        $row = $byDescription->get($description);

                        return [
                            'description' => $description,
                            'label' => $label,
                            'url' => $row?->file ? Storage::disk($row->file->disk)->url($row->file->path) : null,
                        ];
                    })->values(),
                ];
            })->values(),
            'upload_url' => route('weapons.photos.store', $weapon),
        ]);
    }
}
