<?php
//namespace GeneralFunctions;

class ControlTerm
{
    private static $arrchars = [];
    private static $arrprops = [];
    private static $arrsets = [];

    public static function InitPreTerms($chars, $props, $sets)
    {
        self::$arrchars = explode(",", $chars);
        self::$arrprops = explode(",", $props);
        self::$arrsets = explode(",", $sets);

        usort(self::$arrchars, "self::cmp_length");
        usort(self::$arrprops, "self::cmp_length");
        usort(self::$arrsets, "self::cmp_length");

        // var_dump(self::$arrprops);
    }

    private static function cmp_length($a, $b)
    {
        if (strlen($a) == strlen($b)) return 0;

        return (strlen($a) > strlen($b))? -1 : 1;
    }

	public static function checkInControlTerms($input)
    {
        // echo $input;
        // var_dump(self::$arrprops);
        // return  self::checkInControlTermsProps($input);
	    return self::checkInControlTermsSets(self::checkInControlTermsProps(self::checkInControlTermsChar($input)));
	}

    public static function checkInControlTermsChar($sentence)
    {
        return self::AddQuoteToControllTerms($sentence, self::$arrchars);
    }

    public static function checkInControlTermsProps($sentence)
    {
        return self::AddQuoteToControllTerms($sentence, self::$arrprops);
    }

    public static function checkInControlTermsSets($sentence)
    {
        return self::AddQuoteToControllTerms($sentence, self::$arrsets);
    }

    private static function AddQuoteToControllTerms($sentence, $target)
    {
        if (count($target) == 0) return $sentence;
        foreach ($target as $s)
        {
            // echo $s . "\r\n";
            if (trim($s) == "") continue;
            if (strpos($sentence, $s) != false)
            {
                $sentence = str_replace("$s", "\"" . $s . "\"", $sentence);
            }
        }

        return $sentence;
    }

    // function foo(&$var)

    public static function postProcess(&$lKV)
    {
        self::postProcessProps($lKV);
        self::postProcessSets($lKV);

        // var_dump($lKV);
    }

    public static function postProcessProps(&$lKV)
    {
        for($i = 0; $i< count($lKV); $i++)
        {
            if (in_array($lKV[$i][0], self::$arrprops))
            {
                $lKV[$i][1] = "Na";
            }
        }
    }

    public static function postProcessSets(&$lKV)
    {
        for($i = 0; $i< count($lKV); $i++)
        {
            if (in_array($lKV[$i][0], self::$arrsets))
            {
                $lKV[$i][1] = "Nc";
            }
        }

    }
}


abstract class FindModelFilePref
{
    const No = 1;
    const Yes = 2;
    const YesAndSimilar = 3;
}

abstract class AssetType
{
    const Avata = 1;
    const Mocap = 2;
    const Model = 3;
    const Scene = 4;
}

abstract class DbMode
{
    const Remote = 1;
    //Local,
    const Fake = 2;
}


abstract class AvataModelScene
{
    const Avata = 1;
    const Model = 2;
    const Scene = 3;
}