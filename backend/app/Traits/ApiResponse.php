<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\CursorPaginator;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Success', int $status = Response::HTTP_OK, array $meta = []): JsonResponse
    {
        return response()->json(array_filter([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta ?: null,
        ]), $status);
    }

    protected function error(string $message = 'Error', int $status = Response::HTTP_BAD_REQUEST, $errors = null): JsonResponse
    {
        return response()->json(array_filter([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ]), $status);
    }

    protected function notFound(string $message = 'Not found', int $status = Response::HTTP_NOT_FOUND): JsonResponse
    {
        return response()->json(array_filter([
            'status'  => 'not found',
            'message' => $message
        ]), $status);
    }

    protected function created($data = null): JsonResponse
    {
        return $this->success($data, 'Created successfully', Response::HTTP_CREATED);
    }

    protected function paginate(
        mixed $paginator,
        string $message = 'Success',
        ?string $resourceClass = null,
    ): JsonResponse
    {
        $pagination = $paginator instanceof CursorPaginator
            ? [
                'per_page'    => $paginator->perPage(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'has_more'    => ! is_null($paginator->nextCursor()),
            ]
            : [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ];

        $items = $paginator->items();
        $data = $resourceClass === null
            ? $items
            : $resourceClass::collection($items)->resolve(request());

        return $this->success(
            $data,
            $message,
            Response::HTTP_OK,
            ['pagination' => $pagination]
        );
    }
}
