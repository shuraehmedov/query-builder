<?php
use QueryBuilder\DB;
include 'autoloader.php';

$users = DB::table('users')
    ->select('users.id', 'users.name', 'users.email')
    ->where('users.email', '=', 'admin@gmail.com')
    ->where(function ($q){
        return $q->where('users.name', '=', 'Shura')
            ->where(function ($q){
                return $q->where('users.old_years','<','30')->orWhere('users.old_years','>','40');
            });
    })
    ->join('products as p', function ($query) {
        $query->on('users.id', '=', 'p.user_id')->where('p.name', 'admin')->orWhere('p.name', '=', 'quest');
    })
    ->groupBy('users.name')
    ->orderBy('users.name', 'desc')
    ->get();

var_dump($users);