<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class ImportedRecord extends Model
{
protected $table = 'imported_records';


protected $fillable = [
'filename',
'name','email','phone','address','city','state','zip','dob','gender','occupation',
'user_id',
];
}