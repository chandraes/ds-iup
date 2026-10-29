<?php

namespace Tests\Feature;

use App\Http\Controllers\BillingController;
use App\Http\Controllers\RekapController;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class RouteCompatibilityTest extends TestCase
{
    public function test_route_names_are_unique_and_route_collection_can_be_cached(): void
    {
        $routes = app('router')->getRoutes();
        $names = [];
        foreach ($routes as $route) {
            if ($route->getName() !== null) {
                $this->assertArrayNotHasKey($route->getName(), $names, 'Duplicate route name: '.$route->getName());
                $names[$route->getName()] = true;
            }
        }
        $this->assertNotEmpty($routes->compile());
    }

    #[TestWith(['/rekap/invoice-penjualan', 'invoice_penjualan'])]
    #[TestWith(['/rekap/invoice-penjualan/1081/detail', 'invoice_penjualan_detail'])]
    #[TestWith(['/rekap/invoice-penjualan/pdf', 'invoice_penjualan_download'])]
    public function test_invoice_routes_keep_their_controller_and_role_protection(string $uri, string $action): void
    {
        $route = app('router')->getRoutes()->match(Request::create($uri, 'GET'));
        $this->assertSame(RekapController::class.'@'.$action, $route->getActionName());
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('role:su,admin,user,investor', $route->gatherMiddleware());
    }

    public function test_return_deletion_keeps_post_and_role_protection(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/billing/form-barang-retur/delete/12', 'POST'));
        $this->assertSame(BillingController::class.'@form_barang_retur_delete', $route->getActionName());
        $this->assertSame('12', $route->parameter('retur'));
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('role:su,admin,user', $route->gatherMiddleware());
    }

    #[TestWith(['/rekap/invoice-penjualan/detail/1081', 'GET', NotFoundHttpException::class])]
    #[TestWith(['/billing/form-barang-retur/delete/12', 'DELETE', MethodNotAllowedHttpException::class])]
    public function test_unused_duplicate_endpoints_are_removed(string $uri, string $method, string $exception): void
    {
        $this->expectException($exception);
        app('router')->getRoutes()->match(Request::create($uri, $method));
    }

    public function test_existing_named_links_keep_their_urls(): void
    {
        $this->assertSame('/rekap/invoice-penjualan', route('rekap.invoice-penjualan', [], false));
        $this->assertSame('/rekap/invoice-penjualan/1081/detail', route('rekap.invoice-penjualan.detail', ['invoice' => 1081], false));
        $this->assertSame('/billing/form-barang-retur/delete/12', route('billing.form-barang-retur.delete', ['retur' => 12], false));
    }
}
