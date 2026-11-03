<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/bootstrap.php';

$m=$_SERVER['REQUEST_METHOD']??'GET';

try
{
    $model=ModelFactory::products();
    switch($m)
    {
        case 'GET':
        apiResponse(
            200,
            [
                'success'=>true,
                'data'=>$model->GetAllProducts()
                ]
                );
                
        case 'POST':
            $d=requestData();
            $id=$model->CreateProduct((int)($d['CategorieID']??0),isset($d['PhotoURL'])&&$d['PhotoURL']!==''?(string)$d['PhotoURL']:null,(string)($d['ProductName']??$d['Product name']??''),isset($d['Description'])?(string)$d['Description']:null,(float)($d['Price']??-1),(int)($d['Quantity']??-1));apiResponse(201,['success'=>true,'message'=>'Product created.','data'=>$model->GetProductById($id)]);default:header('Allow: GET, POST');apiResponse(405,['success'=>false,'message'=>'Method not allowed.']);}}catch(Throwable $e){handleError($e);}