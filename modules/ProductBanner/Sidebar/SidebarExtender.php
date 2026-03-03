<?php

namespace Modules\ProductBanner\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('product::sidebar.products'), function (Item $item) {
                $item->item(trans('product_banner::sidebar.product_banners'), function (Item $item) {
                    $item->weight(31);
                    $item->route('admin.product_banners.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.product_banners.index')
                    );
                });
            });
        });
    }
}
