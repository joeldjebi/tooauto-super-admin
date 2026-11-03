<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Retire l'indentation des pages HTML (plus de la moitie du poids des listes du call center).
 * Seules les suites d'espaces contenant un retour a la ligne sont reduites a un retour a la ligne :
 * le rendu HTML et le JavaScript ne changent pas. <textarea> et <pre> sont laisses intacts.
 */
class CompactHtml
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->isHtml($response)) {
            return $response;
        }

        $html = $response->getContent();
        $protected = [];

        $html = preg_replace_callback('#<(textarea|pre)\b.*?</\1>#is', function ($matches) use (&$protected) {
            $protected[] = $matches[0];

            return "\x1A" . (count($protected) - 1) . "\x1A";
        }, $html);

        $html = preg_replace('/[ \t]*\n\s*/', "\n", $html);

        $html = preg_replace_callback("/\x1A(\\d+)\x1A/", fn ($matches) => $protected[(int) $matches[1]], $html);

        if (is_string($html)) {
            $response->setContent($html);
        }

        return $response;
    }

    private function isHtml(Response $response): bool
    {
        return ! $response->isRedirection()
            && method_exists($response, 'getContent')
            && is_string($response->getContent())
            && str_contains((string) $response->headers->get('Content-Type', 'text/html'), 'text/html');
    }
}
