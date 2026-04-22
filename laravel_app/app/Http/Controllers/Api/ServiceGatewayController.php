<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ServiceResourceGateway;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use LogicException;

class ServiceGatewayController extends Controller
{
    public function __construct(private ServiceResourceGateway $gateway)
    {
    }

    public function catalog(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'services' => $this->gateway->catalog(),
        ]);
    }

    public function serviceCatalog(string $service): JsonResponse
    {
        try {
            return response()->json(array_merge([
                'ok' => true,
            ], $this->gateway->catalogForService($service)));
        } catch (InvalidArgumentException $exception) {
            return $this->notFoundResponse($exception->getMessage());
        }
    }

    public function aliasIndex(Request $request, string $resource): JsonResponse
    {
        return $this->index($request, (string) $request->route('service'), $resource);
    }

    public function aliasShow(Request $request, string $resource, string $id): JsonResponse
    {
        return $this->show((string) $request->route('service'), $resource, $id);
    }

    public function aliasStore(Request $request, string $resource): JsonResponse
    {
        return $this->store($request, (string) $request->route('service'), $resource);
    }

    public function aliasUpdate(Request $request, string $resource, string $id): JsonResponse
    {
        return $this->update($request, (string) $request->route('service'), $resource, $id);
    }

    public function aliasDestroy(Request $request, string $resource, string $id): JsonResponse
    {
        return $this->destroy((string) $request->route('service'), $resource, $id);
    }

    public function index(Request $request, string $service, string $resource): JsonResponse
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $page = max(1, (int) $request->query('page', 1));

        try {
            return response()->json(array_merge([
                'ok' => true,
            ], $this->gateway->listRecords($service, $resource, $page, $limit)));
        } catch (InvalidArgumentException $exception) {
            return $this->notFoundResponse($exception->getMessage());
        } catch (QueryException $exception) {
            return $this->databaseErrorResponse($exception);
        }
    }

    public function show(string $service, string $resource, string $id): JsonResponse
    {
        try {
            return response()->json(array_merge([
                'ok' => true,
            ], $this->gateway->getRecord($service, $resource, $id)));
        } catch (InvalidArgumentException $exception) {
            return $this->notFoundResponse($exception->getMessage());
        } catch (QueryException $exception) {
            return $this->databaseErrorResponse($exception);
        }
    }

    public function store(Request $request, string $service, string $resource): JsonResponse
    {
        $payload = $request->json()->all();
        if (!is_array($payload) || $payload === []) {
            return response()->json([
                'ok' => false,
                'message' => 'Request body must be a non-empty JSON object.',
            ], 422);
        }

        try {
            return response()->json(array_merge([
                'ok' => true,
                'message' => 'Record created successfully.',
            ], $this->gateway->createRecord($service, $resource, $payload)), 201);
        } catch (InvalidArgumentException $exception) {
            return $this->notFoundResponse($exception->getMessage());
        } catch (LogicException $exception) {
            return $this->methodNotAllowedResponse($exception->getMessage());
        } catch (QueryException $exception) {
            return $this->databaseErrorResponse($exception);
        }
    }

    public function update(Request $request, string $service, string $resource, string $id): JsonResponse
    {
        $payload = $request->json()->all();
        if (!is_array($payload) || $payload === []) {
            return response()->json([
                'ok' => false,
                'message' => 'Request body must be a non-empty JSON object.',
            ], 422);
        }

        try {
            return response()->json(array_merge([
                'ok' => true,
                'message' => 'Record updated successfully.',
            ], $this->gateway->updateRecord($service, $resource, $id, $payload)));
        } catch (InvalidArgumentException $exception) {
            return $this->notFoundResponse($exception->getMessage());
        } catch (LogicException $exception) {
            return $this->methodNotAllowedResponse($exception->getMessage());
        } catch (QueryException $exception) {
            return $this->databaseErrorResponse($exception);
        }
    }

    public function destroy(string $service, string $resource, string $id): JsonResponse
    {
        try {
            return response()->json(array_merge([
                'ok' => true,
                'message' => 'Record deleted successfully.',
            ], $this->gateway->deleteRecord($service, $resource, $id)));
        } catch (InvalidArgumentException $exception) {
            return $this->notFoundResponse($exception->getMessage());
        } catch (LogicException $exception) {
            return $this->methodNotAllowedResponse($exception->getMessage());
        } catch (QueryException $exception) {
            return $this->databaseErrorResponse($exception);
        }
    }

    private function notFoundResponse(string $message): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'message' => $message,
        ], 404);
    }

    private function databaseErrorResponse(QueryException $exception): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'message' => 'Database operation failed.',
            'error' => $exception->getMessage(),
        ], 500);
    }

    private function methodNotAllowedResponse(string $message): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'message' => $message,
        ], 405);
    }
}