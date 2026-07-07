<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

abstract class BaseController
{
    protected function validate(Request $request, array $rules): ?array
    {
        $validator = new Validator();
        $data = $request->body();
        if (!$validator->validate($data, $rules)) {
            $errors = $validator->errors();
            $first = '';
            foreach ($errors as $field => $messages) {
                $first = (string) ($messages[0] ?? '');
                break;
            }
            $message = $first !== '' ? 'Validation failed: ' . $first : 'Validation failed';
            Response::error($message, 422, $errors);
            return null;
        }
        return $data;
    }

    protected function requireRole(Request $request, array $roles): bool
    {
        if (!in_array($request->userRole(), $roles, true)) {
            Response::error('Forbidden', 403);
            return false;
        }
        return true;
    }
}
