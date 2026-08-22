<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemessaBoleto extends Model
{
    protected $fillable = [
        'remessa_id', 'boleto_id'
    ];

    public function remessa(){
		return $this->belongsTo(Remessa::class, 'remessa_id');
	}

	public function boleto(){
		return $this->belongsTo(Boleto::class, 'boleto_id');
	}

}
