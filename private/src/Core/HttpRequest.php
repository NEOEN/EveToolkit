<?php

namespace Core;


class HttpRequest {

    protected $security;
    protected $files = [];
    protected $var = [];
	protected $uriParameters = [];

    /**
     * sets the varsecurity
     *
     * @param VarSecurity $security
     */
    public function __construct(VarSecurity $security) {
        $this->security = $security;
    }

    /**
     * get the var to the method and type after options and filter
     *
     * @param string $methodName post|get|cookie|server
     * @param string $varName
     * @param string $typeName   int|float|url|email|boolean|ip|encoded|special_chars|magic_quotes|string
     * @param string $flag       nullonfailure|ipv4|ipv6|noprivrange|noresrange|array
     * @param mixed  $defaultReturn
     * @param string $option     min|max|decimal
     * @param bool   $filter
     *
     * @return mixed
     */
    public function getVar($methodName, $varName, $typeName = null, $flag = null, $defaultReturn = null, $option = null, $filter = false) {
        if (isset($this->var[$methodName][$varName])) {
            return $this->var[$methodName][$varName];
        }

        $methodIdentifier = $this->getMethod($methodName);
        if (!filter_has_var($methodIdentifier, $varName)) {
            $this->var[$methodName][$varName] = $defaultReturn;

            return $this->var[$methodName][$varName];
        }

        $typeId = $this->security->getType($typeName, $filter);
        $options = $this->security->getOption($option, $flag);
        if ($methodName === 'cookie') {
            $varTmp = filter_input($methodIdentifier, $varName, \FILTER_SANITIZE_STRING);
        } else {
            $varTmp = filter_input($methodIdentifier, $varName, $typeId, $options);
        }
        if ($typeName !== 'boolean' && $typeName !== 'bool' && $varTmp === false) {
            $this->var[$methodName][$varName] = $defaultReturn;
        } else {
            $this->var[$methodName][$varName] = $varTmp;
        }

        return $this->var[$methodName][$varName];
    }

    /**
     * get the int for the method
     *
     * @param string $method
     *
     * @return int
     */
    protected function getMethod($method) {
        switch (strtolower($method)) {
            case 'post':
                $type = INPUT_POST;
                break;
            case 'get':
                $type = INPUT_GET;
                break;
            case 'cookie':
                $type = INPUT_COOKIE;
                break;
            case 'server':
                $type = INPUT_SERVER;
                break;
            #case 'env':
            #	$type = \INPUT_ENV;
            #	break;
            #case 'request':
            #	$type = \INPUT_REQUEST;
            #	break;
            default:
                $type = INPUT_GET;
                break;
        }

        return $type;
    }

	public function getUriParameters(){
		if(empty($this->uriParameters)) {
			$uri = parse_url($this->getVar('server', 'REQUEST_URI', 'string'));
			$this->uriParameters = explode('/', substr($uri['path'], 1) );
		}

		return $this->uriParameters;
	}

    /**
     * get the fileVars
     *
     * @param $fileName
     *
     * @return bool
     */
    public function getFilesVar($fileName) {
        if (isset($this->files[$fileName])) {
            return $this->files[$fileName];
        }

        if (empty($_FILES[$fileName])) {
            return false;
        }

        $fileCount = count($_FILES[$fileName]['name']);
        if ($fileCount > 1) {
            for ($i = 0; $i < $fileCount; $i++) {
                $this->files[$fileName][$i]['name'] = $_FILES[$fileName]['name'][$i];
                $this->files[$fileName][$i]['type'] = $_FILES[$fileName]['type'][$i];
                $this->files[$fileName][$i]['tmp_name'] = $_FILES[$fileName]['tmp_name'][$i];
                $this->files[$fileName][$i]['error'] = $_FILES[$fileName]['error'][$i];
                $this->files[$fileName][$i]['size'] = $_FILES[$fileName]['size'][$i];
            }
        } else {
            $this->files[$fileName][0]['name'] = $_FILES[$fileName]['name'];
            $this->files[$fileName][0]['type'] = $_FILES[$fileName]['type'];
            $this->files[$fileName][0]['tmp_name'] = $_FILES[$fileName]['tmp_name'];
            $this->files[$fileName][0]['error'] = $_FILES[$fileName]['error'];
            $this->files[$fileName][0]['size'] = $_FILES[$fileName]['size'];
        }

        return $this->files[$fileName];
    }
}