<?php
declare(strict_types=1);require_once dirname(__DIR__).'/bootstrap.php';
$m=$_SERVER['REQUEST_METHOD']??'GET';
try{$model=ModelFactory::categories();switch($m){
case 'GET':$rows=$model->GetAllCategories();apiResponse(200,['success'=>true,'data'=>$rows]);
case 'POST':$d=requestData();$id=$model->CreateCategorie((string)($d['name']??$d['CategorieName']??''));apiResponse(201,['success'=>true,'message'=>'Category created.','data'=>$model->GetCategorieById($id)]);
default:header('Allow: GET, POST');apiResponse(405,['success'=>false,'message'=>'Method not allowed.']);}}catch(Throwable $e){handleError($e);}