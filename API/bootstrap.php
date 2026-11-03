<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
require_once dirname(__DIR__).'/backend/ModelFactory.php';
function apiResponse(int $status,array $payload): void { http_response_code($status);echo json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit; }
function requestData(): array
{
    $raw=file_get_contents('php://input');if($raw===false||trim($raw)==='')return $_POST;
    $data=json_decode($raw,true);if(!is_array($data))apiResponse(400,['success'=>false,'message'=>'Request body must be valid JSON.']);return $data;
}
function requestId(): int
{
    $id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);if($id===false||$id===null||$id<1)apiResponse(400,['success'=>false,'message'=>'A valid positive ID is required.']);return $id;
}
function handleError(Throwable $e): void
{
    error_log((string)$e);
    if($e instanceof InvalidArgumentException)apiResponse(422,['success'=>false,'message'=>$e->getMessage()]);
    if($e instanceof DomainException)apiResponse(409,['success'=>false,'message'=>$e->getMessage()]);
    if($e instanceof PDOException && (string)$e->getCode()==='23000')apiResponse(409,['success'=>false,'message'=>'The record conflicts with existing data.']);
    apiResponse(500,['success'=>false,'message'=>'An unexpected server error occurred. Check the server error log.']);
}
