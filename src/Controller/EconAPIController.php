<?php
/**
* @file
* Contains \Drupal\econ_api\Controller\TestAPIController.
*/

namespace Drupal\econ_api\Controller;

use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
* Controller routines for test_api routes.
*/
class EconAPIController extends ControllerBase {

/**
* Callback for `econ-api/get.json` API method.
*/
public function get_econ( Request $request ) {

$response['data'] = 'Some test data to return';
$response['method'] = 'GET';

return new JsonResponse( $response );
}

/**
* Callback for `econ-api/put.json` API method.
*/
public function put_econ( Request $request ) {

$response['data'] = 'Some test data to return';
$response['method'] = 'PUT';

return new JsonResponse( $response );
}

/**
* Callback for `econ-api/post.json` API method.
*/
public function post_econ( Request $request ) {

// This condition checks the `Content-type` and makes sure to
// decode JSON string from the request body into array.
if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
$data = json_decode( $request->getContent(), TRUE );
$request->request->replace( is_array( $data ) ? $data : [] );
}

$response['data'] = 'Some test data to return';
$response['method'] = 'POST';

return new JsonResponse( $response );
}

/**
* Callback for `econ-api/delete.json` API method.
*/
public function delete_econ( Request $request ) {

$response['data'] = 'Some test data to return';
$response['method'] = 'DELETE';

return new JsonResponse( $response );
}

}
