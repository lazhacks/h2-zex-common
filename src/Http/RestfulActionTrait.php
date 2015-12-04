<?php
/**
 * RestfulActionTrait
 *
 * PHP Version 5.3
 *
 * @author    Mark DaRe <aminal@mark-dare.com>
 * @copyright 2015 Mark DaRe (http://www.mark-dare.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://github.com/aminal/zex-restful-action-trait
 */

namespace Common\Http;

use Zend\Http\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RestfulActionTrait
 * @package Common\Http
 */
trait RestfulActionTrait
{
    /**
     * Identifier name used for route or query parameter matching
     *
     * @var string
     */
    public $identifier = 'id';

    /**
     * Custom verb mapping
     *
     * @var array
     */
    private $verbs = [
        Request::METHOD_POST  => 'create',
        Request::METHOD_PATCH => 'update',
        Request::METHOD_PUT   => 'replace',
    ];

    /**
     * Methods supporting list capabilities
     *
     * @var array
     */
    private $hasList = [
        Request::METHOD_GET,
        Request::METHOD_PUT,
        Request::METHOD_PATCH,
        Request::METHOD_DELETE,
    ];

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return mixed
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $method = $request->getMethod();
        $verb   = strtolower($method);

        if (array_key_exists($method, $this->verbs)) {
            $verb = $this->verbs[$method];
        }

        if (in_array($method, $this->hasList) && !$this->hasIdentifier($request)) {
            $verb .= 'List';
        }

        if (method_exists($this, $verb)) {
            return call_user_func_array([$this, $verb], func_get_args());
        }

        throw new Exception\NotImplementedException($verb);
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function hasIdentifier(ServerRequestInterface $request)
    {
        return !!$request->getAttribute($this->identifier);
    }

    /**
     * @param ServerRequestInterface $request
     * @return array|null
     */
    public function getIdentifier(ServerRequestInterface $request)
    {
        if (!$this->hasIdentifier($request)) {
            return null;
        }

        return $request->getAttribute($this->identifier);
    }

    /**
     * Create a resource
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function create(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->notAllowedAction($request, $response, $next);
    }

    /**
     * Get a single resource
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    protected function get(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->notAllowedAction($request, $response, $next);
    }

    /**
     * Get a list of resources
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function getList(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->notAllowedAction($request, $response, $next);
    }

    /**
     * Replace a single resource
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function replace(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->notAllowedAction($request, $response, $next);
    }

    /**
     * Replace a list of resources
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function replaceList(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->notAllowedAction($request, $response, $next);
    }

    /**
     * Update a portion of a single resource
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function update(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->notAllowedAction($request, $response, $next);
    }

    /**
     * Update portions of a list of resources
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function updateList(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->notAllowedAction($request, $response, $next);
    }

    /**
     * Delete a resource
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function delete(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->notAllowedAction($request, $response, $next);
    }

    /**
     * Delete a list of resources
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function deleteList(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->notAllowedAction($request, $response, $next);
    }

    /**
     * Get metadata for a resource
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function head(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->notAllowedAction($request, $response, $next);
    }

    /**
     * Return requested options data
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function options(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->notAllowedAction($request, $response, $next);
    }

    /**
     * Default response for unimplemented methods
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function notAllowedAction(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $response->withStatus(405, 'Method Not Allowed');
    }

    /**
     * Basic not found response
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function notFoundAction(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $response->withStatus(404, 'Not Found');
    }
}
