<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\PublicContentRepository;

class PublicController extends BaseController
{
    public function __construct(private PublicContentRepository $content) {}

    public function mentors(Request $request): void
    {
        Response::success($this->content->getMentors());
    }

    public function testimonials(Request $request): void
    {
        Response::success($this->content->getTestimonials());
    }

    public function resources(Request $request): void
    {
        Response::success($this->content->getFreeResources($request->query('type')));
    }

    public function contact(Request $request): void
    {
        $data = $this->validate($request, [
            'name'    => 'required|min:2',
            'email'   => 'required|email',
            'message' => 'required|min:10',
        ]);
        if (!$data) return;

        $id = $this->content->saveContactMessage(array_merge($request->body(), $data));
        Response::success(['id' => $id], 'Message sent', 201);
    }
}
