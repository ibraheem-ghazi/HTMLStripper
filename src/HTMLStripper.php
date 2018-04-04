<?php

namespace IbraheemGhazi\HTMLStripper;

/**
 * Class HTMLStripper strip tags and filter inline css attribute based on key or (key-value)
 * usage: HTMLStripper->strip($str,true,['strong','em','p'])
 */
class HTMLStripper{

    const EQUAL = '=';
    const LESS_THAN = '<';
    const GREATER_THAN = '>';

    /**
     * @var array what css style attribute are allowed with what values
     */
    private  $allowedStyles= [
//        'css-attribute'=>[
//            'any_value_with_numeric_key_will_use_condition_=',//same as putting values inside '=' but for easy direct use (as we usually use '=')
//             '='=>'', OR '='=>[],
//             '<'=>'', OR '<'=>[],
//             '>'=>'', OR '>'=>[],
//        ],
//        ...
    ];


        public function __construct()
        {
            // $this->addStyle('margin-left','1000px',static::LESS_THAN);
            // $this->addStyle('margin-right','1000px',static::LESS_THAN);

            // $this->addStyle('border','1');
            // $this->addStyle('width','*');
            // $this->addStyle('height','*');
            // $this->addStyle('text-align','*');
            // $this->addStyle('vertical-align','*');
            // $this->addStyle('font-size','*');

            // $this->addStyle('border-collapse','collapse');

            // $this->addStyle('font-weight','bold');
            // $this->addStyle('font-weight','normal');

            // $this->addStyle('font-style','italic');
            // $this->addStyle('font-style','normal');

            // $this->addStyle('text-decoration','underline');
            // $this->addStyle('text-decoration','none');
            // $this->addStyle('text-decoration','line-through');

            // $this->addStyle('list-style-type','none');
            // $this->addStyle('list-style-type','square');
            // $this->addStyle('list-style-type','disc');
            // $this->addStyle('list-style-type','circle');
            // $this->addStyle('list-style-type','upper-alpha');
            // $this->addStyle('list-style-type','lower-alpha');
            // $this->addStyle('list-style-type','upper-roman');
            // $this->addStyle('list-style-type','lower-roman');
            // $this->addStyle('list-style-type','decimal');
            // $this->addStyle('list-style-type','greek');


            // $this->addStyle('direction','ltr');
            // $this->addStyle('direction','rtl');


        }

    /**
     * @param $attribute    string    css attribute (ex: width, height, text-align, ...etc)
     * @param $value        string    css value
     * @param $condition    string    will value be allowed if equal OR less than OR greater than (allowed Values: > < =)
     */
     function addStyle($attribute,$value='*',$condition='='){
        $condition =  in_array($condition,['=','<','>'])?$condition:'=';
        $this->allowedStyles[$attribute][$condition][]=$value;
    }

    /**
     * @param $str              string      input string to strip styles and tags from it
     * @param $useStripTags     bool        should use strip_tags
     * @param $allowedTags      array       what html tags will be allowed
     * @return mixed return a string of filtered and striped version of input string
     */
     function strip($str, $useStripTags=true, $allowedTags= ['p','span','b','strong','u','ins','i','em','s','del','ul','li','ol','table','thead','tfoot','tbody','tr','th','td','br']){

        $new_str = $str;


        if($useStripTags){
            $new_str = strip_tags($str, $this->convertTagsArrayToStrings($allowedTags));
        }

        $pattern = '/<[^>]+ (style=".*?")/i';
        preg_match_all($pattern,$new_str,$matches);

        foreach($matches[1] as $inlineStyle){

            $parsed_style = $this->parseStyle($inlineStyle);
            $cleaned_parsed_style  = $this->removeIllegalStyles($parsed_style);
            $new_style= $this->rebuildInlineCSSFromArray($cleaned_parsed_style);
            $new_str =  str_replace($inlineStyle,$new_style,$new_str);
        }
        return str_replace(' style=""','',$new_str);
    }

    /**
     * convert array of html tags to string in format like : <p><strong><em><ul><li> ...etc
     * @param $allowedTags  array
     * @return string
     */
    private  function convertTagsArrayToStrings(array $allowedTags){
        if(count($allowedTags))
            return '<' . str_replace(',',  '><' ,implode(',',$allowedTags)  ) . '>';

        return '';
    }

    /**
     * convert array of styles with attribute as key and css value as array value to inline css style attribute
     * @param  $parsed_style    array
     * @return string inline style html attribute
     */
    private  function rebuildInlineCSSFromArray(array $parsed_style){
        $new_style_arr= [];
        foreach($parsed_style as $key=>$value){
            $new_style_arr[]="{$key}:{$value}";
        }
        return "style=\"".implode(';',$new_style_arr)."\"";
    }

    /**
     * filter array of css style and remove any not-allowed style attribute regardless of it's value
     * @param array $parsed_style
     * @return array filtered array that removed any not-allowed style attribute regardless of it's value
     */
    private  function removeIllegalStyles(array $parsed_style){
        $cleaned_parsed_style=[];
        foreach($parsed_style as $key=>$value){
            if($this->validateCSSElement($key,$value)){
                $cleaned_parsed_style[$key]=$value;
            }
        }
        return $cleaned_parsed_style;
    }

    /**
     *  check css attribute value if equal , less than, or greater than or any value is allowed
     * @param $key      string      css attribute
     * @param $value    string      css value
     * @return bool is it allowed or not
     */
    private  function validateCSSElement($key,$value){

        if(array_key_exists(strtolower($key),$this->allowedStyles)){

            $any_value_allowed = $this->checkAnyValueAllowed($key);

            $equal_value = $this->checkEqualValue($key,$value);

            $less_than = $this->checkLessThanValue($key,$value);

            $greater_than = $this->checkGreaterThanValue($key,$value);

            return $any_value_allowed || $equal_value || $less_than || $greater_than;
        }

        return false;
    }

    /**
     * @param $key      string      css attribute
     * @param $value    string      value
     * @return bool
     */
    private  function checkGreaterThanValue($key,$value){

        $greater_than = false;
        if(is_array($this->allowedStyles[$key]))
        {
            $greater_than_array = array_key_exists('>',$this->allowedStyles[$key])?$this->allowedStyles[$key]['>']:[];
            $greater_than_array = is_array($greater_than_array)?$greater_than_array:[$greater_than_array];

            list($value_prefix,$value_suffix) = $this->splitValueParts($value);

            $filtered_allowed_values =  array_filter($greater_than_array,function($v)use($value_prefix,$value_suffix){

                list($asv_pre,$asv_suf) = $this->splitValueParts($v);
                return floatval($value_prefix)>floatval($asv_pre)  && trim($value_suffix)===trim($asv_suf);

            });

            $greater_than =  count($filtered_allowed_values)>0;

        }

        return $greater_than;

    }

    /**
     * @param $key      string      css attribute
     * @param $value    string      css value
     * @return bool
     */
    private function checkLessThanValue($key,$value){

        $less_than = false;
        if(is_array($this->allowedStyles[$key]))
        {
            $less_than_array = array_key_exists('<',$this->allowedStyles[$key])?$this->allowedStyles[$key]['<']:[];
            $less_than_array = is_array($less_than_array)?$less_than_array:[$less_than_array];

            list($value_prefix,$value_suffix) = $this->splitValueParts($value);

            $filtered_allowed_values =  array_filter($less_than_array,function($v)use($value_prefix,$value_suffix){

                list($asv_pre,$asv_suf) = $this->splitValueParts($v);
                return floatval($value_prefix)<floatval($asv_pre)  && trim($value_suffix)===trim($asv_suf);

            });

            $less_than =  count($filtered_allowed_values)>0;

        }

        return $less_than;

    }

    /**
     * split css value to numeric value as prefix and string part (px % em rem ...etc) as suffix
     * @param $value    string      css value
     * @param $withKeys        bool         should return numeric indexed array to be able to use it will list() function or return array with named keys
     * @return array
     */
    private  function splitValueParts($value,$withKeys=false){

        $value_suffix = preg_replace('/[\d\+-]*/u', '', $value); //remove any number from value (remains value are something like: px, %, rem, ...etc)

        $value_prefix = str_replace($value_suffix,'',$value); //removing suffix will keep only numeric

        return $withKeys? compact('value_prefix','value_suffix') : [$value_prefix,$value_suffix];

    }

    /**
     * @param $key      string      css attribute
     * @param $value    string      css value
     * @return bool
     */
    private  function checkEqualValue($key,$value){
        $equal_value = false;

        if(array_key_exists(strtolower($key),$this->allowedStyles) && is_array($this->allowedStyles[$key]))
        {
            //if value is equal
            $equal_1 = array_filter($this->allowedStyles[$key],'is_numeric',ARRAY_FILTER_USE_KEY);
            $equal_2 = array_key_exists('=',$this->allowedStyles[$key])?$this->allowedStyles[$key]['=']:[];
            $equal_2 = is_array($equal_2)?$equal_2:[$equal_2];//if '='=>'' or '='=>[]
            $equal =  array_map('trim',array_merge($equal_1,$equal_2));//trim spaces from values
            unset($equal_1);
            unset($equal_2);
            $equal_value =  in_array(trim($value),$equal);
        }
        return $equal_value;
    }

    /**
     * check if attribute allow any value
     * @param $key      string      css attribute
     * @return bool
     */
    private  function checkAnyValueAllowed($key){
        return
            array_key_exists(strtolower($key),$this->allowedStyles) &&
            (
                is_string($this->allowedStyles[$key]) && trim($this->allowedStyles[$key])==='*' ||
                isset($this->allowedStyles[$key]['=']) && count($this->allowedStyles[$key]['=']) && in_array('*',array_map('trim',$this->allowedStyles[$key]['=']))
            );
    }

    /**
     * convert style="*" to array of css attributes and values
     * @param $style    string      inline css style html attribute
     * @return array
     */
    private  function parseStyle($style){
        $cleaned_style_value = $this->cleanStyleValue($style);
        $style_arr = array_filter(explode(';',$cleaned_style_value));
        $keyValArr = [];
        foreach($style_arr as $style){
            try{//try-catch or check if index 1 is exists
                list($key,$value) = explode(':',$style);
                $keyValArr[trim($key)] = trim($value);
            }catch(\Exception $e){}
        }
        return $keyValArr;
    }

    //TODO: enhance cleanStyleValue function

    /**
     * clean style from new lines leafs spaces
     * @param $style
     * @return mixed|null|string|string[]
     */
    private  function cleanStyleValue($style){//input in format style=".*"
        $style_val =  str_replace(['style','='],'',$style);//remove "style=" string from $style
        $style_val = trim($style_val);//trim any spaces remians
        $style_val = trim($style_val,"\"");//remove " from prefix or suffix
        $style_val = ltrim($style_val,"\'");//remove " from prefix only as it may cause error when last style not ending with ; and has value of string //TODO:fix it
        $style_val = preg_replace('/\s+/', '',$style_val);//remove new lines if exists
        return $style_val;
    }


}