<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\AnnouncementRepositoryInterface;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\BrochureRepositoryInterface;
use App\Repositories\Contracts\CollectionEntryRepositoryInterface;
use App\Repositories\Contracts\DealerRepositoryInterface;
use App\Repositories\Contracts\DistrictRepositoryInterface;
use App\Repositories\Contracts\DivisionRepositoryInterface;
use App\Repositories\Contracts\FaqRepositoryInterface;
use App\Repositories\Contracts\GpsLogRepositoryInterface;
use App\Repositories\Contracts\HolidayRepositoryInterface;
use App\Repositories\Contracts\InquiryRepositoryInterface;
use App\Repositories\Contracts\NewsRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\SalesTeamRepositoryInterface;
use App\Repositories\Contracts\ServiceCenterRepositoryInterface;
use App\Repositories\Contracts\TargetRepositoryInterface;
use App\Repositories\Contracts\TerritoryRepositoryInterface;
use App\Repositories\Contracts\ThanaRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\VisitPlanRepositoryInterface;
use App\Repositories\Contracts\VisitRepositoryInterface;
use App\Repositories\Contracts\VisitRequestRepositoryInterface;
use App\Repositories\Eloquent\AnnouncementRepository;
use App\Repositories\Eloquent\AttendanceRepository;
use App\Repositories\Eloquent\BrochureRepository;
use App\Repositories\Eloquent\CollectionEntryRepository;
use App\Repositories\Eloquent\DealerRepository;
use App\Repositories\Eloquent\DistrictRepository;
use App\Repositories\Eloquent\DivisionRepository;
use App\Repositories\Eloquent\FaqRepository;
use App\Repositories\Eloquent\GpsLogRepository;
use App\Repositories\Eloquent\HolidayRepository;
use App\Repositories\Eloquent\InquiryRepository;
use App\Repositories\Eloquent\NewsRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ProductCategoryRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\PromotionRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\SalesTeamRepository;
use App\Repositories\Eloquent\ServiceCenterRepository;
use App\Repositories\Eloquent\TargetRepository;
use App\Repositories\Eloquent\TerritoryRepository;
use App\Repositories\Eloquent\ThanaRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\VisitPlanRepository;
use App\Repositories\Eloquent\VisitRepository;
use App\Repositories\Eloquent\VisitRequestRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds every {Entity}RepositoryInterface to its Eloquent implementation.
 * Each module adds one line here when it introduces a new repository pair.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected array $repositoryBindings = [
        AnnouncementRepositoryInterface::class => AnnouncementRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
        RoleRepositoryInterface::class => RoleRepository::class,
        SalesTeamRepositoryInterface::class => SalesTeamRepository::class,
        TerritoryRepositoryInterface::class => TerritoryRepository::class,
        DealerRepositoryInterface::class => DealerRepository::class,
        DivisionRepositoryInterface::class => DivisionRepository::class,
        DistrictRepositoryInterface::class => DistrictRepository::class,
        ThanaRepositoryInterface::class => ThanaRepository::class,
        ProductCategoryRepositoryInterface::class => ProductCategoryRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        AttendanceRepositoryInterface::class => AttendanceRepository::class,
        GpsLogRepositoryInterface::class => GpsLogRepository::class,
        VisitPlanRepositoryInterface::class => VisitPlanRepository::class,
        VisitRepositoryInterface::class => VisitRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
        CollectionEntryRepositoryInterface::class => CollectionEntryRepository::class,
        TargetRepositoryInterface::class => TargetRepository::class,
        InquiryRepositoryInterface::class => InquiryRepository::class,
        VisitRequestRepositoryInterface::class => VisitRequestRepository::class,
        NewsRepositoryInterface::class => NewsRepository::class,
        PromotionRepositoryInterface::class => PromotionRepository::class,
        FaqRepositoryInterface::class => FaqRepository::class,
        ServiceCenterRepositoryInterface::class => ServiceCenterRepository::class,
        BrochureRepositoryInterface::class => BrochureRepository::class,
        HolidayRepositoryInterface::class => HolidayRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->repositoryBindings as $interface => $concrete) {
            $this->app->bind($interface, $concrete);
        }
    }
}
