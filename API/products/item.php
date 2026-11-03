<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$id = requestId();
$m  = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $model = ModelFactory::products();

    switch ($m) {
        case 'GET':
            $r = $model->GetProductById($id);

            if (!$r) {
                apiResponse(404, [
                    'success' => false,
                    'message' => 'Product not found.',
                ]);
            }

            apiResponse(200, [
                'success' => true,
                'data'    => $r,
            ]);

        case 'PUT':
        case 'PATCH':
            $d = requestData();

            if (
                !$model->UpdateProduct(
                    $id,
                    (int) ($d['CategorieID'] ?? 0),
                    isset($d['PhotoURL']) && $d['PhotoURL'] !== ''
                        ? (string) $d['PhotoURL']
                        : null,
                    (string) ($d['ProductName'] ?? $d['Product name'] ?? ''),
                    isset($d['Description'])
                        ? (string) $d['Description']
                        : null,
                    (float) ($d['Price'] ?? -1),
                    (int) ($d['Quantity'] ?? -1)
                )
            ) {
                apiResponse(404, [
                    'success' => false,
                    'message' => 'Product not found.',
                ]);
            }

            apiResponse(200, [
                'success' => true,
                'message' => 'Product updated.',
                'data'    => $model->GetProductById($id),
            ]);

        case 'DELETE':
            if (!$model->DeleteProduct($id)) {
                apiResponse(404, [
                    'success' => false,
                    'message' => 'Product not found.',
                ]);
            }

            apiResponse(200, [
                'success' => true,
                'message' => 'Product deleted.',
            ]);

        default:
            header('Allow: GET, PUT, PATCH, DELETE');

            apiResponse(405, [
                'success' => false,
                'message' => 'Method not allowed.',
            ]);
    }
} catch (Throwable $e) {
    handleError($e);
}