<?php

namespace App\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Kyslik\ColumnSortable\Sortable;

class Store extends Authenticatable
{
    use Sortable;

    protected $table = 'stores';

    protected $fillable = [
        'name',
        'mobile',
        'company_id',
        'api_url',
        'last_api_updated_at',
        'company_name',
        'email',
        'address',
        'latitude',
        'longitude',
        'city',
        'state',
        'country',
        'pin',
        'status',
        'schedule_active',
        'password',
        'deleted',
        'api_url',
        'blocked_timeslots',
        'back_margin',
        'jeeb_groceries'
    ];

    public $sortable = ['id', 'name', 'mobile', 'email'];

    public function getStorekeeper()
    {
        return $this->hasMany('App\Model\Storekeeper', 'fk_store_id', 'id');
    }
    
    public function getCompany()
    {
        return $this->belongsTo('App\Model\Company', 'company_id');
    }

    public function getProducts()
    {
        return $this->hasMany('App\Model\Product', 'fk_company_id', 'company_id');
    }

    public function getAllProducts() 
    {
        return $this->getProducts()->where('deleted','=', 0);
    }

    public function getBaseProducts()
    {
        return $this->hasMany('App\Model\BaseProduct', 'fk_store_id', 'id');
    }

    public function getAllBaseProducts()
    {
        return $this->getBaseProducts();
    }

    public function getBaseProductsStore()
    {
        return $this->hasMany('App\Model\BaseProductStore', 'fk_store_id', 'id');
    }

    public function getAllBaseProductsStore()
    {
        return $this->getBaseProductsStore();
    }

    public function getOrders()
    {
        return $this->hasMany('App\Model\OrderProduct', 'fk_store_id', 'id');
    }

    public function getOrderProducts()
    {
        return $this->hasMany('App\Model\OrderProduct', 'fk_store_id', 'id');
    }

    public function getSlots() {
        return $this->hasMany('App\Model\StoreSchedule', 'fk_store_id', 'id');
    }

    public function getSlotsByDay($day) {
        return $this->getSlots()->where(['day'=>$day,'deleted'=>0])->get();
    }

    public function getSlotsByDay_24hours_open($day) {
        return $this->getSlots()->where(['day'=>$day,'24hours_open'=>1,'deleted'=>0, 'from'=>NULL, 'to'=>NULL])->count();
    }

    public function getSlotsByDay_24hours_close($day) {
        return $this->getSlots()->where(['day'=>$day,'24hours_open'=>0,'deleted'=>0, 'from'=>NULL, 'to'=>NULL])->count();
    }

    public function getStoreDriver()
    {
        return $this->hasOne('App\Model\DriverGroup', 'fk_store_id');
    }

}
