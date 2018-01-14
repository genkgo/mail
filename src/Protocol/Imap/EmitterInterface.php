<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

interface EmitterInterface
{
    /**
     * @param RequestInterface $request
     * @return Response
     */
    public function emit(RequestInterface $request): Response;

}