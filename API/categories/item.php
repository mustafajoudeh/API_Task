<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/bootstrap.php';

$id=requestId();
$m=$_SERVER['REQUEST_METHOD']??'GET';

try
{
    $model=ModelFactory::categories();
    switch($m)
    {
        case 'GET':
            $r=$model->GetCategorieById($id);
            if(!$r)apiResponse(404,['success'=>false,'message'=>'Category not found.']);
            apiResponse(200,['success'=>true,'data'=>$r]);
            case 'PUT':
                case 'PATCH':$d=requestData();
                if(!$model->UpdateCategorie($id,(string)($d['name']??$d['CategorieName']??'')))
                    apiResponse(404,['success'=>false,'message'=>'Category not found.']);
                apiResponse(200,['success'=>true,'message'=>'Category updated.','data'=>$model->GetCategorieById($id)]);
                case 'DELETE':
                    if(!$model->DeleteCategorie($id))apiResponse(404,['success'=>false,'message'=>'Category not found.']);
                    apiResponse(200,['success'=>true,'message'=>'Category deleted.']);
                    default:
                    header('Allow: GET, PUT, PATCH, DELETE');
                    apiResponse(405,['success'=>false,'message'=>'Method not allowed.']);
                    }
                    }
                    catch(Throwable $e)
                    {
                        handleError($e);
                        }