<?php
namespace Jy;


class Annotation
{
    private $rawDocBlock;
    private $className;
	private $parameters;
	private $keyPattern = "[A-z0-9\_\-]+";
	private $endPattern = "[ ]*(?:@|\r\n|\n)";
	private $parsedAll = FALSE;

	public function setClassName(string $className)
    {
        $this->className = $className;

        return $this;
	}

    // 修改结束符
    public function setEndPattern(string $pattern)
    {
        $this->endPattern = $pattern;

        return $this;
    }

    // type : method | property | class
    public function resolve(string $class, string $name, string $type = 'class')
    {
        $this->setClassName($class);

        if ('method' == $type) {
            $reflection = new \ReflectionMethod($this->className, $name);
        } else if ('class' == $type) {
            $reflection = new \ReflectionClass($this->className);
        } else if ('property' == $type) {
            $reflection = new \ReflectionProperty($this->className, $name);
        } else {
            throw new \Exception('param 3 only support : method | property | class');
        }

        $this->rawDocBlock = $reflection->getDocComment();
        $this->parameters = array();

        return $this;
    }

	private function parseSingle($key)
	{
		if(isset($this->parameters[$key]))
		{
			return $this->parameters[$key];
		}
		else
		{
			if(preg_match("/@".preg_quote($key).$this->endPattern."/", $this->rawDocBlock, $match))
			{
				return TRUE;
			}
			else
			{
				preg_match_all("/@".preg_quote($key)." (.*)".$this->endPattern."/U", $this->rawDocBlock, $matches);
				$size = sizeof($matches[1]);
				if($size === 0)
				{
					return NULL;
				}
				elseif($size === 1)
				{
					return $this->parseValue($matches[1][0]);
				}
				else
				{
					$this->parameters[$key] = array();
					foreach($matches[1] as $elem)
					{
						$this->parameters[$key][] = $this->parseValue($elem);
					}
					return $this->parameters[$key];
				}
			}
		}
	}

	private function parse()
	{
		$pattern = "/@(?=(.*)".$this->endPattern.")/U";
        $this->rawDocBlock = str_ireplace(["/", "*", "\r\n", "\n", "\r", "\t"], "", (string) $this->rawDocBlock);
        $this->rawDocBlock = trim($this->rawDocBlock). "\n";
		preg_match_all($pattern, $this->rawDocBlock, $matches);
        //$matches['doc'] = $this->rawDocBlock;
        //$matches['pattern'] = $pattern;
        //print_r($matches);
		foreach($matches[1] as $rawParameter)
		{
			if(preg_match("/^(".$this->keyPattern.") (.*)$/", $rawParameter, $match))
			{
				$parsedValue = $this->parseValue($match[2]);
				if(isset($this->parameters[$match[1]]))
				{
					$this->parameters[$match[1]] = array_merge((array)$this->parameters[$match[1]], (array)$parsedValue);
				}
				else
				{
					$this->parameters[$match[1]] = $parsedValue;
				}
			}
			else if(preg_match("/^".$this->keyPattern."$/", $rawParameter, $match))
			{
				$this->parameters[$rawParameter] = TRUE;
			}
			else
			{
				$this->parameters[$rawParameter] = NULL;
			}
		}
	}

	public function getVariableDeclarations($name)
	{
		$declarations = (array)$this->getParameter($name);
		foreach($declarations as &$declaration)
		{
			$declaration = $this->parseVariableDeclaration($declaration, $name);
		}
		return $declarations;
	}

	private function parseVariableDeclaration($declaration, $name)
	{
		$type = gettype($declaration);
		if($type !== 'string')
		{
			throw new \InvalidArgumentException(
				"Raw declaration must be string, $type given. Key='$name'.");
		}
		if(strlen($declaration) === 0)
		{
			throw new \InvalidArgumentException(
				"Raw declaration cannot have zero length. Key='$name'.");
		}
		$declaration = explode(" ", $declaration);
		if(sizeof($declaration) == 1)
		{
			array_unshift($declaration, "string");
		}
		$declaration = array(
			'type' => $declaration[0],
			'name' => $declaration[1]
		);
		return $declaration;
	}

	private function parseValue($originalValue)
	{
		if($originalValue && $originalValue !== 'null')
		{
			if( ($json = json_decode($originalValue,TRUE)) === NULL)
			{
				$value = $originalValue;
			}
			else
			{
				$value = $json;
			}
		}
		else
		{
			$value = NULL;
		}
		return $value;
	}

	public function getParameters()
	{
		$this->parse();

		return $this->parameters;
	}

	public function getParameter($key)
	{
		return $this->parseSingle($key);
	}
}
