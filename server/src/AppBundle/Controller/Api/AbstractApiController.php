<?php

namespace GPS\AppBundle\Controller\Api;

use GPS\AppBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\Context;
use JMS\Serializer\SerializationContext;
use AC\WebServicesBundle\Serializer\DeserializationContext;
use AC\WebServicesBundle\ServiceResponse;
use AC\WebServicesBundle\Exception\ValidationException;

/**
 * Base controller for API requests with convenience methods.
 *
 * @package GPS
 * @author Evan Villemez
 */
class AbstractApiController extends AbstractController
{
    /**
     * Shortcut to validating objects, throwing exceptions when invalid.
     *
     * @param mixed $obj
     * @throws HttpException
     * @return null
     */
    protected function validate($obj)
    {
        $errors = $this->get('validator')->validate($obj);

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
    }

    /**
     * Convenience method for decoding incoming API data.  The data format is determined via
     * a negotiation service, and then deserialized.
     *
     * @return mixed
     */
    protected function decodeRequest(Request $request, $class, Context $ctx = null)
    {
        $container = $this->container;
        $serializerFormat = $container->get('ac_web_services.negotiator')->negotiateRequestFormat($request);

        $data = $request->getContent();

        //check for raw form submission, php is stupid about this, so there needs to be a check for it here
        if ('form' === $serializerFormat && $container->getParameter('ac_web_services.serializer.enable_form_deserialization')) {
            $data = $request->request->all();
            if (empty($data)) {
                if (
                    0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
                    &&
                    in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
                ) {
                    parse_str($request->getContent(), $data);
                }
            }
        }

        return $container->get('jms_serializer')->deserialize($data, $class, $serializerFormat, $ctx);
    }

    /**
     * Some API requests are not directly associated with models, in which case the raw
     * json is decoded and processed manually.
     *
     * @param Request $req
     * @param array $requiredFields
     */
    protected function decodeRawJsonRequest(Request $req, array $requiredFields = [])
    {
        $data = @json_decode($req->getContent(), true);

        //ensure it was decoded
        if (!$data || json_last_error() !== JSON_ERROR_NONE) {
            throw $this->createHttpException(422, sprintf("The submitted JSON was improperly formatted: %s.", json_last_error_msg()));
        }

        //check for missing fields if specified
        if (!empty($requiredFields)) {
            $missing = [];

            foreach ($requiredFields as $name) {
                if (!isset($data[$name])) {
                    $missing[] = $name;
                }
            }

            if (!empty($missing)) {
                throw $this->createHttpException(422, sprintf("The following fields are required, but were missing: %s", implode(',', $missing)));
            }
        }

        return $data;
    }

    /**
     * Shortcut to creating a JMS SerializationContext
     *
     * @return SerializationContext
     */
    protected function createSerializationContext()
    {
        return SerializationContext::create();
    }

    /**
     * Shorcut to creating an ACWebServices DeserializationContext
     *
     * @return DeserializationContext
     */
    protected function createDeserializationContext()
    {
        return DeserializationContext::create();
    }

    /**
     * Shortcut to create a ServiceResponse
     *
     * @param  string          $data
     * @param  string          $code
     * @param  array           $headers
     * @param  string          $template
     *
     * @return ServiceResponse
     */
    protected function createServiceResponse($data, $code = 200, $headers = [], $template = null)
    {
        $localCache = $this->container->get('gps.local_cache');
        $headers = array_merge($headers, [
            'x-gps-deployed-at' => $localCache->fetch('gps.deploy-tag')
        ]);
        
        return new ServiceResponse($data, $code, $headers, $template);
    }
}
