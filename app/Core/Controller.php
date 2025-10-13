<?php

namespace App\Core;

abstract class Controller
{
    public function __construct(
        protected Request $request,
        protected View $view,
        protected Response $response,
        protected Session $session,
        protected Auth $auth
    ) {
    }

    protected function render(string $template, array $data = [], ?string $layout = 'layouts/app.php'): Response
    {
        $csrfToken = null;
        if (property_exists($this, 'csrf') && $this->csrf instanceof \App\Core\Csrf) {
            $csrfToken = $this->csrf->token();
        } else {
            $csrfToken = $this->session->get('_csrf_token') ?? null;
        }

        $shared = [
            'authUser' => $this->auth->user(),
            'flash_success' => $this->session->getFlash('success'),
            'flash_error' => $this->session->getFlash('error'),
            'flash_warning' => $this->session->getFlash('warning'),
            'csrfToken' => $csrfToken,
        ];

        $content = $this->view->render($template, array_merge($shared, $data));

        if ($layout !== null) {
            $content = $this->view->render($layout, array_merge($shared, $data, [
                'content' => $content,
                'title' => $data['title'] ?? null,
            ]));
        }

        return $this->response->setContent($content);
    }

    protected function redirect(string $url): Response
    {
        return $this->response->setStatus(302)->header('Location', $url);
    }
}
