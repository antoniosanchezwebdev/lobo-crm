<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatGpt extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'whatsapp_mensaje_chatgpt';

    /**
     * Atributos asignados en masa.
     *
     * @var array
     */
    protected $fillable = [
        'id_mensaje',
        'remitente',
        'mensaje',
        'respuesta',
        'status',
        'type'
        
    ];
}
