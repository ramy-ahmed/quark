<?php
namespace Quark\Extensions\Quark\REST;

use Quark\IQuarkDataProvider;
use Quark\IQuarkExtension;
use Quark\IQuarkModel;
use Quark\IQuarkModelWithCustomCollectionName;
use Quark\IQuarkModelWithCustomPrimaryKey;

use Quark\Quark;
use Quark\QuarkArchException;
use Quark\QuarkDTO;
use Quark\QuarkHTTPClient;
use Quark\QuarkKeyValuePair;
use Quark\QuarkModel;
use Quark\QuarkModelSource;
use Quark\QuarkObject;
use Quark\QuarkURI;
use Quark\QuarkJSONIOProcessor;

/**
 * Class RESTService
 *
 * @package Quark\Extensions\Quark\REST
 */
class RESTService implements IQuarkDataProvider, IQuarkExtension {
	const OPTION_PAGE = 'page';
	const OPTION_RESPONSE = '___rest_response___';
	const OPTION_LOWER_COLLECTION = '___lower_collection___';

	/**
	 * @var QuarkURI
	 */
	private $_uri = null;

	/**
	 * @param string $connection
	 * @param string $method
	 * @param string $action
	 * @param mixed $data
	 *
	 * @return \stdClass
	 * @throws QuarkArchException
	 */
	public static function DirectCommand ($connection, $method, $action, $data = []) {
		try {
			$source = Quark::Stack($connection);
		}
		catch (\Exception $e) {
			$source = null;
		}

		if (!($source instanceof QuarkModelSource))
			throw new QuarkArchException('Specified connection for RESTService::DirectCommand is not a valid QuarkModelSource');

		return self::_ll_api($source->URI(), $method, $action, $data);
	}

	/**
	 * @param QuarkURI $uri
	 *
	 * @return void
	 */
	public function Connect (QuarkURI $uri) {
		$this->_uri = $uri;
	}

	/**
	 * @param QuarkURI $uri
	 * @param string $method
	 * @param string $action
	 * @param mixed $data = []
	 * @param mixed $options = []
	 *
	 * @return \stdClass
	 * @throws QuarkArchException
	 */
	private static function _ll_api (QuarkURI $uri, $method, $action, $data = [], $options = []) {
		$request = new QuarkDTO(new QuarkJSONIOProcessor());
		$request->Method($method);
		$request->Merge($data);

		$response = new QuarkDTO(new QuarkJSONIOProcessor());

		$uri->path = $action;

		if ($method == QuarkDTO::METHOD_GET)
			$uri->AppendQuery($data);

		$uri = $uri->URI(true);

		/**
		 * @var \stdClass $data
		 */
		$data = QuarkHTTPClient::To($uri, $request, $response);

		if ($data == null || !isset($data->status) || $data->status != 200)
			throw new QuarkArchException(
				'[' . $uri . '] QuarkRest API is not reachable. ' . "\r\n" .
				'--- [REQUEST.RAW] ---' .  "\r\n" .
				$request->SerializeRequest() . "\r\n" .
				'--- [REQUEST.DATA] ---' .  "\r\n" .
				print_r($request->Data(), true) .  "\r\n" .
				'--- [RESPONSE.RAW] ---' . "\r\n" .
				$response->Raw() . "\r\n" .
				'--- [RESPONSE.DATA] ---' . "\r\n" .
				print_r($response->Data(), true) .  "\r\n"
			);

		if (isset($options[self::OPTION_RESPONSE])) {
			$after_response = $options[self::OPTION_RESPONSE];

			if (is_callable($after_response))
				$after_response($data);
		}

		return $data;
	}

	/**
	 * @param string $method
	 * @param string $action
	 * @param mixed $data = []
	 * @param mixed $options = []
	 *
	 * @return mixed
	 * @throws QuarkArchException
	 */
	private function _api ($method, $action, $data = [], $options = []) {
		if (!$this->_uri)
			throw new QuarkArchException('QuarkRest API is not reachable. URI is not provided');

		return self::_ll_api($this->_uri, $method, $action, $data, $options);
	}

	/**
	 * @param mixed $criteria
	 *
	 * @return mixed
	 */
	public function Login ($criteria = []) {
		try {
			return $this->_api('POST', '/user/login', $criteria)->profile;
		}
		catch (QuarkArchException $e) {
			Quark::Log($e->message, $e->lvl);
			return false;
		}
	}

	/**
	 * @param IQuarkModel $model
	 *
	 * @return string
	 */
	private function _pk (IQuarkModel $model) {
		return $model instanceof IQuarkModelWithCustomPrimaryKey
			? $model->PrimaryKey()
			: '_id';
	}

	/**
	 * @param IQuarkModel $model
	 *
	 * @return string
	 */
	private function _identify (IQuarkModel $model) {
		$pk = $this->_pk($model);

		return isset($model->$pk) ? $model->$pk : null;
	}

	/**
	 * http://php.net/manual/ru/function.preg-split.php#104602
	 *
	 * @param IQuarkModel $model
	 * @param $options
	 * @param string $implode = '/'
	 *
	 * @return string
	 */
	private static function _class (IQuarkModel $model, $options = [], $implode = '/') {
		if ($model instanceof IQuarkModelWithCustomCollectionName)
			return $model->CollectionName();

		if (isset($options[QuarkModel::OPTION_COLLECTION]))
			return $options[QuarkModel::OPTION_COLLECTION];

		$words = preg_split('/([[:upper:]][[:lower:]]+)/', QuarkObject::ClassOf($model), null, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

		if (sizeof($words) == 1)
			$words[0] = strtolower($words[0]);

		return implode($implode, $words);
	}

	/**
	 * @param IQuarkModel $model
	 * @param $options
	 *
	 * @return mixed
	 */
	public function Create (IQuarkModel $model, $options = []) {
		if (!isset($options[self::OPTION_LOWER_COLLECTION]))
			$options[self::OPTION_LOWER_COLLECTION] = true;

		try {
			$class = self::_class($model, $options);
			$pk = $this->_pk($model);

			$api = $this->_api('POST', '/' . $class . '/create', $model, $options);

			if (!isset($api->status) || $api->status != 200) return false;

			$class = QuarkModel::CollectionName($model, $options);

			if ($options[self::OPTION_LOWER_COLLECTION])
				$class = strtolower($class);

			if (isset($api->$class->$pk))
				$model->$pk = $model->$pk instanceof \MongoId
					? new \MongoId($api->$class->$pk)
					: $api->$class->$pk;

			return true;
		}
		catch (QuarkArchException $e) {
			Quark::Log($e->message, $e->lvl);
			return false;
		}
	}

	/**
	 * @param IQuarkModel $model
	 * @param $options
	 *
	 * @return mixed
	 */
	public function Save (IQuarkModel $model, $options = []) {
		try {
			$api = $this->_api('POST', '/' . self::_class($model, $options) . '/update/' . $this->_identify($model), $model, $options);

			return isset($api->status) && $api->status == 200;
		}
		catch (QuarkArchException $e) {
			Quark::Log($e->message, $e->lvl);
			return false;
		}
	}

	/**
	 * @param IQuarkModel $model
	 * @param $options
	 *
	 * @return mixed
	 */
	public function Remove (IQuarkModel $model, $options = []) {
		try {
			$api = $this->_api('GET', '/' . self::_class($model, $options) . '/remove/' . $this->_identify($model), array(), $options);

			return isset($api->status) && $api->status == 200;
		}
		catch (QuarkArchException $e) {
			Quark::Log($e->message, $e->lvl);
			return false;
		}
	}

	/**
	 * @param IQuarkModel $model
	 *
	 * @return QuarkKeyValuePair
	 */
	public function PrimaryKey (IQuarkModel $model) {
		return new QuarkKeyValuePair($this->_pk($model), '');
	}

	/**
	 * @param IQuarkModel $model
	 * @param array $criteria
	 * @param array $options
	 *
	 * @return array
	 */
	public function Find (IQuarkModel $model, $criteria, $options = []) {
		try {
			if (isset($options[self::OPTION_PAGE]))
				$this->_uri->query .= '&page=' . $options[self::OPTION_PAGE];

			if (isset($options[QuarkModel::OPTION_LIMIT]))
				$this->_uri->query .= '&limit=' . $options[QuarkModel::OPTION_LIMIT];

			$api = $this->_api('Get', '/' . self::_class($model, $options) . '/list', $criteria, $options);

			return isset($api->list) ? $api->list : array();
		}
		catch (QuarkArchException $e) {
			Quark::Log($e->message, $e->lvl);
			return array();
		}
	}

	/**
	 * @param IQuarkModel $model
	 * @param $criteria
	 * @param $options
	 *
	 * @return IQuarkModel|null
	 */
	public function FindOne (IQuarkModel $model, $criteria, $options) {
		$path = self::_class($model, $options);
		$class = self::_class($model, $options, '');

		try {
			return $this->_api('Get', '/' . $path, $criteria, $options)->$class;
		}
		catch (QuarkArchException $e) {
			Quark::Log($e->message, $e->lvl);
			return null;
		}
	}

	/**
	 * @param IQuarkModel $model
	 * @param $id
	 * @param $options
	 *
	 * @return null
	 * @throws QuarkArchException
	 */
	public function FindOneById (IQuarkModel $model, $id, $options) {
		if (!is_scalar($id))
			throw new QuarkArchException('Parameter $id must have scalar value, Given: ' . print_r($id, true));

		$path = self::_class($model, $options);
		$class = self::_class($model, $options, '');

		try {
			return $this->_api('Get', '/' . $path . '/' . $id, array(), $options)->$class;
		}
		catch (QuarkArchException $e) {
			Quark::Log($e->message, $e->lvl);
			return null;
		}
	}

	/**
	 * @param IQuarkModel $model
	 * @param             $criteria
	 * @param             $options
	 *
	 * @return void
	 */
	public function Update (IQuarkModel $model, $criteria, $options) {
		// TODO: Implement Update() method.
	}

	/**
	 * @param IQuarkModel $model
	 * @param             $criteria
	 * @param             $options
	 *
	 * @return void
	 */
	public function Delete (IQuarkModel $model, $criteria, $options) {
		// TODO: Implement Delete() method.
	}

	/**
	 * @param IQuarkModel $model
	 * @param             $criteria
	 * @param             $limit
	 * @param             $skip
	 * @param             $options
	 *
	 * @return void
	 */
	public function Count (IQuarkModel $model, $criteria, $limit, $skip, $options) {
		// TODO: Implement Count() method.
	}

	/**
	 * @param IQuarkModel $model
	 * @param string $command
	 * @param QuarkDTO|object|array $data
	 * @param string $method = 'GET'
	 * @param $options = []
	 *
	 * @return bool
	 */
	public function Command (IQuarkModel $model, $command, $data = [], $method = 'GET', $options = []) {
		try {
			return $this->_api($method, '/' . self::_class($model, $options) . '/' . $command . '/' . $this->_identify($model), $data, $options);
		}
		catch (QuarkArchException $e) {
			Quark::Log($e->message, $e->lvl);
			return false;
		}
	}
}