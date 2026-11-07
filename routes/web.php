<?php

use App\Http\Controllers\AlertsController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\RevistaArmasController;
use App\Http\Controllers\RevistaGuestAuthController;
use App\Http\Controllers\RevistaGuestWeaponController;
use App\Http\Controllers\RevistaPhotoReviewController;
use App\Http\Controllers\RevistaPhotoStagingController;
use App\Http\Controllers\AuthenticatedPermitImageController;
use App\Http\Controllers\Auth\ForcedPasswordChangeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormatController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\WeaponCustodyController;
use App\Http\Controllers\WeaponCustodyReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResponsiblePortfolioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VestController;
use App\Http\Controllers\VestImportController;
use App\Http\Controllers\VestPhotoController;
use App\Http\Controllers\WeaponClientAssignmentController;
use App\Http\Controllers\WeaponController;
use App\Http\Controllers\WeaponDocumentController;
use App\Http\Controllers\WeaponImportController;
use App\Http\Controllers\WeaponIncidentController;
use App\Http\Controllers\WeaponIncidentReportController;
use App\Http\Controllers\WeaponInternalAssignmentController;
use App\Http\Controllers\WeaponPhotoController;
use App\Http\Controllers\WeaponTransferController;
use App\Http\Controllers\WorkerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/empresa/logo', [CompanySettingsController::class, 'logo'])->name('company.logo');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::get('/dashboard/metrics', [DashboardController::class, 'metrics'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.metrics');

Route::prefix('revista-armas')->name('revista-armas.')->group(function () {
    Route::middleware('revista.guest.guest')->group(function () {
        Route::get('/ingreso', [RevistaGuestAuthController::class, 'showLogin'])->name('guest.login');
        Route::post('/ingreso', [RevistaGuestAuthController::class, 'login'])->name('guest.login.store');
    });

    Route::middleware('revista.guest')->group(function () {
        Route::post('/salir', [RevistaGuestAuthController::class, 'logout'])->name('guest.logout');
        Route::get('/mis-armas', [RevistaGuestWeaponController::class, 'index'])->name('guest.weapons.index');
        Route::get('/mis-armas/{weapon}/estado', [RevistaGuestWeaponController::class, 'stagingState'])->name('guest.weapons.staging-state');
        Route::post('/mis-armas/{weapon}/fotos', [RevistaPhotoStagingController::class, 'storeGuest'])->name('guest.weapons.photos.store');
    });
});

Route::post('/locale', function (Request $request) {
    $locale = (string) $request->input('locale', 'es');
    $supportedLocales = ['es', 'en'];

    if (! in_array($locale, $supportedLocales, true)) {
        $locale = 'es';
    }

    $request->session()->put('locale', $locale);

    return back();
})->name('locale.switch');

Route::middleware('auth')->group(function () {
    Route::get('/password/change-required', [ForcedPasswordChangeController::class, 'edit'])->name('password.force.edit');
    Route::put('/password/change-required', [ForcedPasswordChangeController::class, 'update'])->name('password.force.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])
        ->whereUuid('id')
        ->name('notifications.read');

    Route::middleware('module:clients')->group(function () {
        Route::patch('/clients/{client}/restore', [ClientController::class, 'restore'])->name('clients.restore');
        Route::resource('clients', ClientController::class)->except(['show']);
        Route::get('/clients/{client}/installations/create', [InstallationController::class, 'create'])->name('clients.installations.create');
        Route::post('/clients/{client}/installations', [InstallationController::class, 'store'])->name('clients.installations.store');
        Route::get('/installations/{installation}/edit', [InstallationController::class, 'edit'])->name('installations.edit');
        Route::get('/installations/{installation}/datos', [InstallationController::class, 'editForm'])->name('installations.form');
        Route::put('/installations/{installation}', [InstallationController::class, 'update'])->name('installations.update');
        Route::post('/installations/{installation}/puestos', [InstallationController::class, 'storePost'])->name('installations.posts.store');
        Route::post('/installations/{installation}/armerillos', [InstallationController::class, 'storeArmerillo'])->name('installations.armerillos.store');
        Route::patch('/installations/{installation}/puestos/{post}', [InstallationController::class, 'renamePost'])->name('installations.posts.update');
        Route::get('/installations/{installation}/trabajadores', [InstallationController::class, 'searchWorkers'])->name('installations.workers.search');
        Route::post('/installations/{installation}/puestos/{post}/trabajadores', [InstallationController::class, 'assignWorker'])->name('installations.posts.workers.store');
        Route::delete('/installations/{installation}/puestos/{post}/trabajadores/{worker}', [InstallationController::class, 'detachWorker'])->name('installations.posts.workers.destroy');
        Route::get('/installations/{installation}/armas', [InstallationController::class, 'searchWeapons'])->name('installations.weapons.search');
        Route::post('/installations/{installation}/puestos/{post}/armas', [InstallationController::class, 'assignWeapon'])->name('installations.posts.weapons.store');
        Route::delete('/installations/{installation}/puestos/{post}/armas/{weapon}', [InstallationController::class, 'detachWeapon'])->name('installations.posts.weapons.destroy');
        Route::patch('/installations/{installation}/archive', [InstallationController::class, 'archive'])->name('installations.archive');
        Route::patch('/installations/{installation}/restore', [InstallationController::class, 'restore'])->name('installations.restore');
    });

    Route::middleware('module:posts')->group(function () {
        Route::get('/installations', [InstallationController::class, 'index'])->name('installations.index');
        Route::get('/installations/create', [InstallationController::class, 'begin'])->name('installations.create');
        Route::post('/installations', [InstallationController::class, 'save'])->name('installations.store');
        Route::get('/posts/{post}/histories', [PostController::class, 'histories'])->name('posts.histories');
        Route::patch('/posts/{post}/restore', [PostController::class, 'restore'])->name('posts.restore');
        Route::resource('posts', PostController::class)->except(['show']);
    });

    Route::middleware('module:workers')->group(function () {
        Route::get('/workers/{worker}/histories', [WorkerController::class, 'histories'])->name('workers.histories');
        Route::patch('/workers/{worker}/restore', [WorkerController::class, 'restore'])->name('workers.restore');
        Route::resource('workers', WorkerController::class)->except(['show']);
    });

    Route::middleware('module:vests')->group(function () {
        Route::get('/vests/form-options', [VestController::class, 'formOptionsJson'])->name('vests.form-options');
        Route::resource('vests', VestController::class);
        Route::post('/vests/{vest}/photos', [VestPhotoController::class, 'store'])->name('vests.photos.store');
        Route::patch('/vests/{vest}/photos/{photo}', [VestPhotoController::class, 'update'])->name('vests.photos.update');
        Route::delete('/vests/{vest}/photos/{photo}', [VestPhotoController::class, 'destroy'])->name('vests.photos.destroy');
    });

    Route::middleware('module:imports,vests')->group(function () {
    Route::get('/subir-chalecos', [VestImportController::class, 'index'])->name('vest-imports.index');
    Route::get('/subir-chalecos/plantilla', [VestImportController::class, 'downloadTemplate'])->name('vest-imports.templates.vest');
    Route::post('/subir-chalecos/preview', [VestImportController::class, 'preview'])->name('vest-imports.preview');
    Route::get('/subir-chalecos/{vestImportBatch}', [VestImportController::class, 'show'])->name('vest-imports.show');
    Route::post('/subir-chalecos/{vestImportBatch}/execute/start', [VestImportController::class, 'startExecution'])->name('vest-imports.start');
    Route::post('/subir-chalecos/{vestImportBatch}/execute/process', [VestImportController::class, 'processExecution'])->name('vest-imports.process');
    Route::get('/subir-chalecos/{vestImportBatch}/execute/status', [VestImportController::class, 'executionStatus'])->name('vest-imports.status');
    Route::post('/subir-chalecos/{vestImportBatch}/execute', [VestImportController::class, 'execute'])->name('vest-imports.execute');
    Route::post('/subir-chalecos/{vestImportBatch}/discard', [VestImportController::class, 'discard'])->name('vest-imports.discard');
    Route::get('/subir-armas', [WeaponImportController::class, 'index'])->name('weapon-imports.index');
    Route::get('/subir-armas/plantillas/armas', [WeaponImportController::class, 'downloadWeaponTemplate'])->name('weapon-imports.templates.weapon');
    Route::get('/subir-armas/plantillas/clientes', [WeaponImportController::class, 'downloadClientTemplate'])->name('weapon-imports.templates.client');
    Route::get('/subir-armas/plantillas/trabajadores', [WeaponImportController::class, 'downloadWorkerTemplate'])->name('weapon-imports.templates.worker');
    Route::post('/subir-armas/preview', [WeaponImportController::class, 'preview'])->name('weapon-imports.preview');
    Route::get('/subir-armas/{weaponImportBatch}', [WeaponImportController::class, 'show'])->name('weapon-imports.show');
    Route::post('/subir-armas/{weaponImportBatch}/execute/start', [WeaponImportController::class, 'startExecution'])->name('weapon-imports.start');
    Route::post('/subir-armas/{weaponImportBatch}/execute/process', [WeaponImportController::class, 'processExecution'])->name('weapon-imports.process');
    Route::get('/subir-armas/{weaponImportBatch}/execute/status', [WeaponImportController::class, 'executionStatus'])->name('weapon-imports.status');
    Route::post('/subir-armas/{weaponImportBatch}/execute', [WeaponImportController::class, 'execute'])->name('weapon-imports.execute');
    Route::post('/subir-armas/{weaponImportBatch}/discard', [WeaponImportController::class, 'discard'])->name('weapon-imports.discard');
    Route::post('/weapon-imports/permit-authenticated/{permit_kind}', [WeaponImportController::class, 'updatePermitAuthenticated'])
        ->whereIn('permit_kind', ['porte', 'tenencia'])
        ->name('weapon-imports.permit-authenticated.update');
    });

    Route::middleware('module:users')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::post('/users/{user}/send-access-credentials', [UserController::class, 'sendAccessCredentials'])->name('users.send-access-credentials');
    });

    Route::middleware('module:weapons')->group(function () {
    Route::get('/weapons/export-preview', [WeaponController::class, 'exportPreview'])->name('weapons.export.preview');
    Route::get('/weapons/export', [WeaponController::class, 'export'])->name('weapons.export');
    Route::post('/weapons/export-selected', [WeaponController::class, 'exportSelected'])->name('weapons.export.selected');
    Route::get('/weapons/filter-options', [WeaponController::class, 'filterOptions'])->name('weapons.filter_options');
    Route::resource('weapons', WeaponController::class);
    Route::post('/weapons/{weapon}/client-assignments', [WeaponClientAssignmentController::class, 'store'])
        ->name('weapons.client_assignments.store');
    Route::get('/weapons/{weapon}/permit', [WeaponController::class, 'permitPhoto'])
        ->name('weapons.permit');
    Route::patch('/weapons/{weapon}/permit', [WeaponController::class, 'updatePermitPhoto'])
        ->name('weapons.permit.update');
    Route::get('/authenticated-permit-images/{permit_kind}', [AuthenticatedPermitImageController::class, 'show'])
        ->whereIn('permit_kind', ['porte', 'tenencia'])
        ->name('authenticated-permit-images.show');

    Route::post('/weapons/{weapon}/photos', [WeaponPhotoController::class, 'store'])
        ->name('weapons.photos.store');
    Route::patch('/weapons/{weapon}/photos/{photo}', [WeaponPhotoController::class, 'update'])
        ->name('weapons.photos.update');
    Route::delete('/weapons/{weapon}/photos/{photo}', [WeaponPhotoController::class, 'destroy'])
        ->name('weapons.photos.destroy');

    Route::post('/weapons/{weapon}/documents', [WeaponDocumentController::class, 'store'])
        ->name('weapons.documents.store');
    Route::get('/weapons/{weapon}/documents/{document}/download', [WeaponDocumentController::class, 'download'])
        ->name('weapons.documents.download');
    Route::delete('/weapons/{weapon}/documents/{document}', [WeaponDocumentController::class, 'destroy'])
        ->name('weapons.documents.destroy');

    Route::post('/weapon-incidents', [WeaponIncidentController::class, 'store'])
        ->name('weapon-incidents.store');
    Route::post('/weapon-incidents/{weaponIncident}/updates', [WeaponIncidentController::class, 'storeUpdate'])
        ->name('weapon-incidents.updates.store');
    Route::patch('/weapon-incidents/{weaponIncident}/reopen', [WeaponIncidentController::class, 'reopen'])
        ->name('weapon-incidents.reopen');
    Route::patch('/weapon-incidents/{weaponIncident}/close', [WeaponIncidentController::class, 'close'])
        ->name('weapon-incidents.close');
    Route::get('/weapon-incidents/{weaponIncident}/attachment', [WeaponIncidentController::class, 'downloadAttachment'])
        ->name('weapon-incidents.attachment');
    Route::get('/weapon-incidents/{weaponIncident}/updates/{weaponIncidentUpdate}/attachment', [WeaponIncidentController::class, 'downloadUpdateAttachment'])
        ->name('weapon-incidents.updates.attachment');

    Route::post('/weapons/{weapon}/internal-assignments', [WeaponInternalAssignmentController::class, 'store'])
        ->name('weapons.internal_assignments.store');
    Route::patch('/weapons/{weapon}/internal-assignments/retire', [WeaponInternalAssignmentController::class, 'retire'])
        ->name('weapons.internal_assignments.retire');
    Route::post('/weapons/{weapon}/custody/armerillo', [WeaponCustodyController::class, 'moveToArmerillo'])
        ->name('weapons.custody.armerillo');
    Route::post('/weapons/{weapon}/custody/para-mantenimiento', [WeaponCustodyController::class, 'moveToParaMantenimiento'])
        ->name('weapons.custody.para_mantenimiento');
    Route::post('/weapons/{weapon}/custody/armero', [WeaponCustodyController::class, 'moveToArmero'])
        ->name('weapons.custody.armero');
    Route::post('/weapons/{weapon}/custody/armero-posts', [WeaponCustodyController::class, 'storeArmeroPost'])
        ->name('weapons.custody.armero_posts.store');
    Route::patch('/weapons/{weapon}/imprints', [WeaponController::class, 'toggleImprint'])
        ->name('weapons.imprints.toggle');
    });

    Route::middleware('module:transfers')->group(function () {
        Route::get('/transfers', [WeaponTransferController::class, 'index'])->name('transfers.index');
        Route::post('/transfers/bulk', [WeaponTransferController::class, 'bulkStore'])->name('transfers.bulk');
        Route::patch('/transfers/{transfer}/accept', [WeaponTransferController::class, 'accept'])->name('transfers.accept');
        Route::patch('/transfers/{transfer}/cancel', [WeaponTransferController::class, 'cancel'])->name('transfers.cancel');
    });

    Route::middleware('module:map')->group(function () {
        Route::get('/mapa', [MapController::class, 'index'])->name('maps.index');
        Route::get('/mapa/armas', [MapController::class, 'weapons'])->name('maps.weapons');
    });

    Route::middleware('module:portfolios')->group(function () {
        Route::get('/portfolios', [ResponsiblePortfolioController::class, 'index'])->name('portfolios.index');
        Route::get('/portfolios/{user}/edit', [ResponsiblePortfolioController::class, 'edit'])->name('portfolios.edit');
        Route::put('/portfolios/{user}', [ResponsiblePortfolioController::class, 'update'])->name('portfolios.update');
        Route::post('/portfolios/{user}/transfer', [ResponsiblePortfolioController::class, 'transfer'])->name('portfolios.transfer');
    });

    Route::middleware('module:formats')->group(function () {
        Route::get('/formatos', [FormatController::class, 'index'])->name('formatos.index');
        Route::get('/formatos/revista-mensual/vacio', [FormatController::class, 'downloadEmptyMonthlyReview'])->name('formatos.revista-mensual.vacio');
        Route::get('/formatos/revista-mensual/armas', [FormatController::class, 'monthlyReviewWeapons'])->name('formatos.revista-mensual.armas');
        Route::get('/formatos/revista-mensual/opciones-columna', [FormatController::class, 'monthlyReviewColumnOptions'])->name('formatos.revista-mensual.column-options');
        Route::post('/formatos/revista-mensual/vista-previa', [FormatController::class, 'previewMonthlyReview'])->name('formatos.revista-mensual.vista-previa');
        Route::post('/formatos/revista-mensual/descargar', [FormatController::class, 'downloadMonthlyReview'])->name('formatos.revista-mensual.descargar');
        Route::get('/formatos/plantillas/armas', [FormatController::class, 'downloadWeaponTemplate'])->name('formatos.plantillas.armas');
        Route::get('/formatos/plantillas/clientes', [FormatController::class, 'downloadClientTemplate'])->name('formatos.plantillas.clientes');
        Route::get('/formatos/plantillas/trabajadores', [FormatController::class, 'downloadWorkerTemplate'])->name('formatos.plantillas.trabajadores');
    });

    Route::middleware('module:reports')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/assignments', [ReportController::class, 'weaponsByClient'])->name('reports.assignments');
    Route::get('/reports/no-destination', [ReportController::class, 'weaponsWithoutDestination'])->name('reports.no_destination');
    Route::get('/reports/history', [ReportController::class, 'history'])->name('reports.history');
    Route::get('/reports/audit', [ReportController::class, 'audit'])->name('reports.audit');
    });

    Route::middleware('module:reports,weapons')->group(function () {
        Route::get('/reports/weapon-incidents/weapons/search', [WeaponIncidentReportController::class, 'searchWeapons'])->name('reports.weapon-incidents.weapons.search');
        Route::get('/reports/weapon-incidents', [WeaponIncidentReportController::class, 'index'])->name('reports.weapon-incidents.index');
        Route::get('/reports/weapon-incidents/{incidentType}', [WeaponIncidentReportController::class, 'show'])->name('reports.weapon-incidents.show');
        Route::get('/reports/weapon-custody', [WeaponCustodyReportController::class, 'index'])->name('reports.weapon-custody.index');
    });

    Route::middleware(['revista.staff', 'module:revista'])->prefix('revista-armas')->name('revista-armas.')->group(function () {
        Route::get('/', [RevistaArmasController::class, 'index'])->name('index');
        Route::get('/armas/buscar', [RevistaArmasController::class, 'searchAssignableWeapons'])->name('weapons.search');
        Route::post('/enviar', [RevistaArmasController::class, 'assign'])->name('assign');
        Route::get('/armas/{weapon}/fotos', [RevistaArmasController::class, 'photos'])->name('photos');
        Route::post('/armas/{weapon}/revision/{fieldUser}/aprobar', [RevistaPhotoReviewController::class, 'approve'])->name('review.approve');
        Route::post('/armas/{weapon}/revision/{fieldUser}/rechazar', [RevistaPhotoReviewController::class, 'reject'])->name('review.reject');
    });

    Route::middleware('module:company')->group(function () {
        Route::get('/empresa', [CompanySettingsController::class, 'edit'])->name('company.edit');
        Route::put('/empresa', [CompanySettingsController::class, 'update'])->name('company.update');
        Route::get('/empresa/membrete/{part}', [CompanySettingsController::class, 'letterheadImage'])
            ->whereIn('part', ['header', 'footer'])
            ->name('company.letterhead.image');
    });

    Route::middleware('module:settings')->prefix('ajustes')->name('catalogs.')->group(function () {
        Route::get('/', [CatalogController::class, 'index'])->name('index');
        Route::post('/cargos', [CatalogController::class, 'storePosition'])->name('positions.store');
        Route::put('/cargos/{position}', [CatalogController::class, 'updatePosition'])->name('positions.update');
        Route::delete('/cargos/{position}', [CatalogController::class, 'destroyPosition'])->name('positions.destroy');
        Route::post('/cargos/{position}/orden', [CatalogController::class, 'movePosition'])->name('positions.move');
        Route::post('/vinculacion', [CatalogController::class, 'storeEngagementType'])->name('engagement-types.store');
        Route::put('/vinculacion/{engagementType}', [CatalogController::class, 'updateEngagementType'])->name('engagement-types.update');
        Route::delete('/vinculacion/{engagementType}', [CatalogController::class, 'destroyEngagementType'])->name('engagement-types.destroy');
        Route::post('/vinculacion/{engagementType}/orden', [CatalogController::class, 'moveEngagementType'])->name('engagement-types.move');
        Route::post('/contratos', [CatalogController::class, 'storeContractType'])->name('contract-types.store');
        Route::put('/contratos/{contractType}', [CatalogController::class, 'updateContractType'])->name('contract-types.update');
        Route::delete('/contratos/{contractType}', [CatalogController::class, 'destroyContractType'])->name('contract-types.destroy');
        Route::post('/contratos/{contractType}/orden', [CatalogController::class, 'moveContractType'])->name('contract-types.move');
        Route::post('/estructuras', [CatalogController::class, 'storeStructureType'])->name('structure-types.store');
        Route::put('/estructuras/{structureType}', [CatalogController::class, 'updateStructureType'])->name('structure-types.update');
        Route::delete('/estructuras/{structureType}', [CatalogController::class, 'destroyStructureType'])->name('structure-types.destroy');
        Route::post('/estructuras/{structureType}/orden', [CatalogController::class, 'moveStructureType'])->name('structure-types.move');
        Route::post('/marcas', [CatalogController::class, 'storeWeaponBrand'])->name('weapon-brands.store');
        Route::put('/marcas/{weaponBrand}', [CatalogController::class, 'updateWeaponBrand'])->name('weapon-brands.update');
        Route::delete('/marcas/{weaponBrand}', [CatalogController::class, 'destroyWeaponBrand'])->name('weapon-brands.destroy');
        Route::post('/marcas/{weaponBrand}/orden', [CatalogController::class, 'moveWeaponBrand'])->name('weapon-brands.move');
        Route::post('/calibres', [CatalogController::class, 'storeWeaponCaliber'])->name('weapon-calibers.store');
        Route::put('/calibres/{weaponCaliber}', [CatalogController::class, 'updateWeaponCaliber'])->name('weapon-calibers.update');
        Route::delete('/calibres/{weaponCaliber}', [CatalogController::class, 'destroyWeaponCaliber'])->name('weapon-calibers.destroy');
        Route::post('/calibres/{weaponCaliber}/orden', [CatalogController::class, 'moveWeaponCaliber'])->name('weapon-calibers.move');
        Route::post('/novedades', [CatalogController::class, 'storeIncident'])->name('incident-types.store');
        Route::put('/novedades/{incidentType}', [CatalogController::class, 'updateIncidentType'])->name('incident-types.update');
        Route::delete('/novedades/{incidentType}', [CatalogController::class, 'destroyIncidentType'])->name('incident-types.destroy');
        Route::post('/novedades/{incidentType}/orden', [CatalogController::class, 'moveIncidentType'])->name('incident-types.move');
        Route::put('/modalidades/{incidentModality}', [CatalogController::class, 'updateModality'])->name('modalities.update');
        Route::delete('/modalidades/{incidentModality}', [CatalogController::class, 'destroyModality'])->name('modalities.destroy');
        Route::post('/modalidades/{incidentModality}/orden', [CatalogController::class, 'moveModality'])->name('modalities.move');
    });

    Route::middleware('module:alerts')->group(function () {
        Route::get('/alerts/documents', [AlertsController::class, 'documents'])->name('alerts.documents');
        Route::post('/alerts/documents/preview', [AlertsController::class, 'previewBatch'])->name('alerts.documents.preview');
        Route::post('/alerts/documents/download', [AlertsController::class, 'downloadBatch'])->name('alerts.documents.download');
    });
});

require __DIR__.'/auth.php';
