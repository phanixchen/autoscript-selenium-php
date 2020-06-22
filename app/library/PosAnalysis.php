<?php

class PosAnalysis
{
    // private string cnString = "Server=104.199.189.223;Database=autoscript;Uid=sychen;Pwd=Gis92813#;";

    private $lTermPos = []; // = new List<KeyValuePair<string, string>>();



    // public PosAnalysis()
    // {
    //     lTermPos = new List<KeyValuePair<string, string>>();
    // }

    // public string ConnectionString
    // {
    //     get { return cnString; }
    //     set { cnString = value; }
    // }


    private function multiexplode ($delimiters,$data) {
        $MakeReady = str_replace($delimiters, $delimiters[0], $data);
        $Return    = explode($delimiters[0], $MakeReady);
        return  $Return;
    }

    public function addGeneralObjtoArray(&$arrobj, $obj)
    {
        // var_dump($obj);
        // var_dump($arrobj);
        $bfound = false;
        foreach ($arrobj as $o)
        {
            if ($o->name == $obj->name)
            {
                $bfound = true;
                break;
            }
        }

        if ($bfound == false)
            array_push($arrobj, $obj);
        // echo "========================\n";
        //         var_dump($arrobj);

    }

    public function parsetoJson($arrinput)
    {
        $scenes = [];
        $chars = [];
        $sets = [];
        $props = [];
        $charactions = [];

        $charcandidate = [];
        $propcandidate = [];
        $setcandidate = [];
        $mocapcandidate = [];

        $scenen = null;
        $indent = -1;
        foreach ($arrinput as $arr)
        {
            $arr_parse_sentence = preg_split( "/[\||(|)]/", $arr ); // explode by @ and vs

            // var_dump($arr_parse_sentence);
            
            // process sentence by sentence
            for ($i = 0; $i < count($arr_parse_sentence); $i++)
            {
                if (strpos($arr_parse_sentence[$i], "] S") == strlen($arr_parse_sentence[$i]) - 3)
                {
                    if ($scene != null) array_push($scenes, $scene);
                    $scenen = new Scene();

                    $indent = 0;

                    continue;
                }

                switch ($arr_parse_sentence[$i])
                {
                    case "PERIODCATEGORY":
                    case "EXCLAMATIONCATEGORY":
                    case "QUESTIONCATEGORY":
                    case "COMMACATEGORY":
                        var_dump($charcandidate);
                        var_dump($mocapcandidate);
                        var_dump($propcandidate);
                        var_dump($setcandidate);

                        var_dump($chars);
                        var_dump($charactions);
                        var_dump($props);
                        var_dump($sets);

                        $charcandidate = [];
                        $propcandidate = [];
                        $setcandidate = [];
                        $mocapcandidate = [];

                        $charactions = [];
                        break;

                    case "":
                        $indent--;
                        break;
                    
                    default:
                        $arr_parsenode = explode(":", $arr_parse_sentence[$i]);
                        if (count($arr_parsenode) == 2)
                        {
                            $indent++;
                            continue;
                        }
                        if (count($arr_parsenode) == 3)
                        {
                            switch (strtoupper($arr_parsenode[1]))
                            {
                                case "ND":
                                    $scene->timelight = $arr_parsenode[2];
                                    break;

                                case "NB": //人的機會很大
                                case "NBA":
                                case "NH": //代名詞, 你我他
                                    $tmp = new Char($arr_parsenode[2], $i, "dummyfile", "dummyname", $arr_parsenode[1], [], []); // ($_name, $_id, $_asset, $_assetname, $_pos, $_action_list, $_adjunct_descriptions)
                                    $this->addGeneralObjtoArray($chars, $tmp);
                                    array_push($charcandidate, [$i, $indent]);
                                    break;

                                case "NC": //地點場景的機會很大
                                case "NCB":
                                    $tmp = new PropSet($arr_parsenode[2], $i, $arr_parsenode[1], "dummyname", "dummyfile"); //($_name, $_id, $_pos, $_asset, $_assetname)
                                    $this->addGeneralObjtoArray($props, $tmp);
                                    array_push($setcandidate, [$i, $indent]);
                                    break;

                                case "NA": //物品的機會很大
                                case "NAB":
                                    $tmp = new PropSet($arr_parsenode[2], $i, $arr_parsenode[1], "dummyname", "dummyfile"); //($_name, $_id, $_pos, $_asset, $_assetname)
                                    $this->addGeneralObjtoArray($sets, $tmp);
                                    array_push($propcandidate, [$i, $indent]);
                                    break;

                                case "VA":
                                case "VA1":
                                case "VA11":
                                case "VG":
                                case "VD":
                                case "VD1":
                                case "VC":  //及物動詞
                                case "VCL": //有目的地的動詞, 前往、走向
                                case "VE":
                                    $tmp = new CharAction($arr_parsenode[2], -1, $i, $arr_parsenode[1], "dummyfile", "dummyname"); // ($_name, $_target, $_id, $_pos, $_asset, $_assetname)
                                    $this->addGeneralObjtoArray($charactions, $tmp);
                                    array_push($mocapcandidate, [$i, $indent]);
                                    // $iVerb = $i;

                                    break;

                                default:
                                    echo "unexpected: \n";
                                    var_dump($arr_parse_sentence[$i]);
                                    echo "unexpected end.\n";
                                    break;
                            }
                        }
                        break;
                }
                
            }
        }
    }

    public function posConvertToJsonObj($posstring)
    {
        #region convert to keyvalue list
        $posstring = str_replace("\r", "", $posstring);
        $posstring = str_replace("\n", "", $posstring);
        $arr = $this->multiexplode( array("　", "-"), $posstring ); // $output = preg_split( "/ (@|vs) /", $input ); // explode by @ and vs
        $arr = array_filter($arr);
        // var_dump($arr);

        $tmppair = [];
        //char[] sep = new char[] { '(', ')' };
        foreach ($arr as $pair)
        {
            // var_dump($pair);
            if (strpos($pair, "-------") !== false) break;

            $tmppair = array_filter($this->multiexplode( array("(", ")"), trim($pair) ) );
            // var_dump($tmppair);
            $kv = [];
            if (count($tmppair) == 2)
            {
                $kv = [$tmppair[0], $tmppair[1]];
            }
            else
            {
                $kv = [substr($pair, 0, 1), $tmppair[0]];
            }
            
            array_push($this->lTermPos, $kv);
        }
        #endregion
        // var_dump($this->lTermPos);

        #region prcoessing key value list
        //List<string> lJson = new List<string>();
        $lreturn = [];
        $ltmp = [];
        for ($iCounter = 0; $iCounter < count($this->lTermPos); $iCounter++)
        {
            if ($this->lTermPos[$iCounter][1] != "PERIODCATEGORY" && $this->lTermPos[$iCounter][1] != "EXCLAMATIONCATEGORY"
                && $this->lTermPos[$iCounter][1] != "QUESTIONCATEGORY")
            {
                array_push($ltmp, $this->lTermPos[$iCounter]);
            }
            else
            {
                //end of a section
                array_push($ltmp, $this->lTermPos[$iCounter]);

                array_push($lreturn, $ltmp);

                // call function to generate json
                //lJson.Add(cKVlistToJson(ltmp));

                // renew tmp list
                $ltmp = [];
            }
        }
        #endregion

        return $lreturn;
    }


//     private IFindOutFile fofModel;
//     private IFindOutFile fofAvata;
//     private IFindOutFile fofScene;
//     private IFindOutFile fofMocap;

    
    public function cKVlistToJson_v2($lKV, $findPreference, $dbMode)
    {
        // position index pointer
        $iStart = 0;
        $iVerb = -1;
        $iComma = 0;
        $iObj = -1;
        $iSub = -1;
        $iPreObj = -1;
        $iPreSub = -1;

        #region init FindOutFile object
        // if ($dbMode == DbMode::Fake)
        // {
        //     fofModel = new FindOutFileLocal();
        //     fofAvata = new FindOutFileLocal();
        //     fofScene = new FindOutFileLocal();
        //     fofMocap = new FindOutFileLocal();
        // }
        // else
        // {
        //     fofModel = new FindOutFileMysql(cnString);
        //     fofAvata = new FindOutFileMysql(cnString);
        //     fofScene = new FindOutFileMysql(cnString);
        //     fofMocap = new FindOutFileMysql(cnString);
        // }
        #endregion

        $lAofObj = [];
        $lAofSub = [];

        // dictionary key = object (person, object, scene, etc.)
        // dictionary value = <objectid, list (means sequential) of description pairs>
        // a description pair can be <action, target>, <adj/adv, null> etc.
        //Dictionary<string, KeyValuePair<int, List<KeyValuePair<string, string>>>> AnimationComponents =
        //                    new Dictionary<string, KeyValuePair<int, List<KeyValuePair<string, string>>>>();
        //Dictionary<string, KeyValuePair<int, List<object>>> AnimationComponents =
        //                                new Dictionary<string, KeyValuePair<int, List<object>>>();

        $scene = new Scene();
        // array of candidate term index
        $charcandidate = []; //人物  model
        $propcandidate = []; //物品
        $setcandidate = []; //場景
        $mocapcandidate = []; //動作


        // array of objects (char, prop, set)
        $charlist = [];
        $proplist = [];
        $setlist = [];

        for ($i = 0; $i < count($lKV); $i++)
        {

            switch (strtoupper($lKV[$i][1]))
            {
                case "ND":
                    $scene->timelight = $lKV[$i][0];
                    break;

                case "NB": //人的機會很大
                case "NH": //代名詞, 你我他
                    array_push($charcandidate, $i);
                    break;

                case "NC": //地點場景的機會很大
                    array_push($setcandidate, $i);
                    break;

                case "NA": //物品的機會很大
                    array_push($propcandidate, $i);
                    break;

                case "VA":
                case "VG":
                case "VD":
                case "VC":  //及物動詞
                case "VCL": //有目的地的動詞, 前往、走向
                case "VE":
                    array_push($mocapcandidate, $i);
                    $iVerb = $i;

                    break;

                case "PERIODCATEGORY":
                case "EXCLAMATIONCATEGORY":
                case "QUESTIONCATEGORY":
                case "COMMACATEGORY":
                    // meet the end of a sentence
                    $iStart = $i;

                    //處理場景
                    foreach ($setcandidate as $itmp)
                        $this->checkInSetListOrAdd($itmp, $lKV, $setlist, $findPreference);

                    // meet the end of a sub-sentence, process
                    if (count($mocapcandidate) == 0)
                    {
                        // 沒有動詞，代表描述狀態，所以不會有受詞
                        #region 檢查有沒有在 charlist, proplist, 或 setlist, 沒有的話加入
                        foreach ($charcandidate as $itmp)
                            $this->checkInCharListOrAdd($itmp, $lKV, $charlist, $findPreference);

                        if (count($charcandidate) > 0)
                            $iSub = $charcandidate[count($charcandidate) - 1];

                        foreach ($propcandidate as $itmp)
                            $this->checkInPropListOrAdd($itmp, $lKV, $proplist, $findPreference);

                        if (count($propcandidate) > 0)
                            $iSub = $propcandidate[count($propcandidate) - 1];
                        #endregion
                    }
                    else
                    {
                        // 有動詞
                        //for ($itmpverb = 0; itmpverb < mocapcandidate.Count; itmpverb++ )
                        {
                            //iVerb = mocapcandidate[itmpverb];
                            $iVerb = $mocapcandidate[0];

                            #region find out action
                            // __construct($_name, $_target, $_pos, $_asset, $_assetname)
                            // $_action = new CharAction($lKV[$iVerb][0], -1, $lKV[$iVerb][1], "dummyfile", "dummy_actionname");
                            /*fofMocap.Init(findPreference, lKV[iVerb].Key, AssetType.Mocap);
                            fofMocap.find();

                            if (fofMocap.GetResult() != null)
                            {
                                Dictionary<string, string> tmp = fofMocap.GetResult();
                                _action.assetname = tmp["name"];
                                _action.name = tmp["name"];
                                _action.asset = tmp["filename"];
                            }
                            else
                            {
                                _action.assetname = "dummyaction";
                                _action.name = "dummyaction";
                                _action.asset = "dummyaction";
                            }*/
                            #endregion

                            $charaction_tmplist = []; // new List<CharAction>();

                            #region 在動詞之後的都是受詞
                            $bHasObj = false;
                            // foreach ($itmpobj in charcandidate)
                            foreach ($charcandidate as $itmpobj)
                            {
                                if ($itmpobj > $iVerb)
                                {
                                    $_action = new CharAction($lKV[$iVerb][0], -1, $lKV[$iVerb][1], "dummyfile", "dummy_actionname");
                                    // check in char list
                                    $this->checkInCharListOrAdd($itmpobj, $lKV, $charlist, $findPreference);

                                    $_action->target = $itmpobj;
                                    array_push($charaction_tmplist, $_action);

                                    //set iobj as the last object
                                    $iObj = max($iObj, $itmpobj);

                                    $bHasObj = true;
                                }
                            }
                            // foreach ($itmpobj in propcandidate)
                            foreach ($propcandidate as $itmpobj)
                            {
                                if ($itmpobj > $iVerb)
                                {
                                    $_action = new CharAction($lKV[$iVerb][0], -1, $lKV[$iVerb][1], "dummyfile", "dummy_actionname");
                                    // check in char list
                                    $this->checkInCharListOrAdd($itmpobj, $lKV, $proplist, $findPreference);

                                    $_action->target = $itmpobj;
                                    array_push($charaction_tmplist, $_action);

                                    //set iobj as the last object
                                    $iObj = max($iObj, $itmpobj);

                                    $bHasObj = true;
                                }
                            }

                            //如果都沒有受詞，還是要補進 list
                            if ($bHasObj == false)
                            {
                                array_push($charaction_tmplist, $_action);
                            }
                            #endregion

                            #region 理論上在動詞之前的都是主詞
                            $bHasSub = false;
                            foreach ($charcandidate as $itmpobj)
                            {
                                if ($itmpobj < $iVerb)
                                {
                                    // check in char list
                                    $iFound = $this->checkInCharListOrAdd($itmpobj, $lKV, $charlist, $findPreference);

                                    foreach ($charaction_tmplist as $ca)
                                        array_push($charlist[$iFound]->actions, $ca);

                                    //set iSub as the last subject
                                    $iSub = max($iSub, $itmpsub);

                                    $bHasSub = true;
                                }
                            }

                            foreach ($propcandidate as $itmpobj)
                            {
                                if ($itmpobj < $iVerb)
                                {
                                    // check in char list
                                    $iFound = $this->checkInCharListOrAdd($itmpobj, $lKV, $charlist, $findPreference);

                                    foreach ($charaction_tmplist as $ca)
                                        array_push($charlist[$iFound]->actions, $ca);

                                    //set iSub as the last subject
                                    $iSub = max($iSub, $itmpsub);

                                    $bHasSub = true;
                                }
                            }
                            

                            //沒有找到主詞，那就是前一句的主詞
                            if ($bHasSub == false)
                            {
                                $iFound = $this->checkInCharListOrAdd($iPreSub, $lKV, $charlist, $findPreference);

                                foreach ($charaction_tmplist as $ca)
                                    array_push($charlist[$iFound]->actions, $ca);
                            }
                            #endregion
                        }
                    }

                    // clean the candidate list
                    $charcandidate = [];
                    $propcandidate = [];
                    $setcandidate = [];
                    $mocapcandidate = [];

                    //set position indexies for next scan
                    //i = Math.Max(iObj, Math.Max(iSub, iVerb)); // + 1; // move i to the last object
                    if ($iObj != -1) $iPreObj = $iObj;
                    if ($iSub != -1) $iPreSub = $iSub;
                    $iSub = -1;
                    $iObj = -1;
                    $iVerb = -1;
                    break;

                
                default:
                    if (substr(strtoupper($lKV[$i][0]), 0, 1) == "N")
                    {

                    }
                    else if (substr(strtoupper($lKV[$i][0]), 0, 1) == "V")
                    {

                    }
                    break;
            }
        }

        $scene->chars = $charlist;
        $scene->props = $proplist;
        $scene->sets = $setlist;

        return $scene->toJson();
    }


    /// <summary>
    /// 檢查是否在 charlist,  沒有的話就找出 ASSET 並加入
    /// </summary>
    /// <param name="charlist"></param>
    /// <param name="lKV"></param>
    /// <param name="charcandidate"></param>
    /// <param name="findPreference"></param>
    private function checkInCharListOrAdd($iCandidateIndex, &$lKV, &$charlist, $findPreference)
    {
        $bfind = false;
        $iFoundIndex = -1;
        //foreach ($iCandidateIndex in charcandidate)
        {
            for ($i = 0; $i < count($charlist); $i++)
            {
                if ($charlist[$i]->name == $lKV[$iCandidateIndex][0])
                {
                    //find!
                    $bfind = true;
                    $iFoundIndex = $i;
                    break;
                }
            }

            if ($bfind == false)
            {
                //find char asset, add to charlist
                /*fofAvata.Init(findPreference, lKV[iCandidateIndex].Key, AssetType.Avata);
                fofAvata.find();

                Char _char = new Char();
                _char.id = iCandidateIndex;
                _char.name = lKV[iCandidateIndex].Key;
                _char.pos = lKV[iCandidateIndex].Value;

                if (fofAvata.GetResult() != null)
                {
                    Dictionary<string, string> tmp = fofAvata.GetResult();
                    _char.assetname = tmp["name"];
                    _char.asset = tmp["filename"];
                }
                else
                {
                    //_char.asset = "no_matched_asset_file";
                    _char.assetname = "dummy";
                    _char.asset = "char_0000";
                }*/

                // __construct($_name, $_id, $_asset, $_assetname, $_pos, $_action_list, $_adjunct_descriptions)
                $_char = new Char($lKV[$iCandidateIndex][0], $iCandidateIndex, "dummyfile", "dummy_assetname", $lKV[$iCandidateIndex][1], [], []);
                array_push($charlist, $_char);
                
                $iFoundIndex = count($charlist) - 1;
            }
        }

        return $iFoundIndex;
        #endregion
    }

    /// <summary>
    /// 檢查是否在 proplist,  沒有的話就找出 ASSET 並加入
    /// </summary>
    /// <param name="propcandidate"></param>
    /// <param name="lKV"></param>
    /// <param name="proplist"></param>
    /// <param name="findPreference"></param>
    private function checkInPropListOrAdd($iCandidateIndex, &$lKV, &$proplist, $findPreference)
    {
        #region 檢查 prop
        $bfind = false;
        $iFoundIndex = -1;

        //foreach ($iCandidateIndex in propcandidate)
        {
            for ($i = 0; $i < count($proplist); $i++)
            {
                // var_dump($proplist[$i]);
                if ($proplist[$i]->name == $lKV[$iCandidateIndex][0])
                {
                    //find!
                    $bfind = true;
                    $iFoundIndex = i;
                    break;
                }
            }

            if ($bfind == false)
            {
                //find char asset, add to charlist
                // fofModel.Init(findPreference, lKV[iCandidateIndex].Key, AssetType.Model);
                // fofModel.find();

                // PropSet _prop = new PropSet();
                // _prop.id = iCandidateIndex;
                // _prop.name = lKV[iCandidateIndex].Key;
                // _prop.pos = lKV[iCandidateIndex].Value;

                // if (fofModel.GetResult() != null)
                // {
                //     Dictionary<string, string> tmp = fofModel.GetResult();
                //     _prop.assetname = tmp["name"];
                //     _prop.asset = tmp["filename"];
                // }
                // else
                // {
                //     //_char.asset = "no_matched_asset_file";
                //     _prop.assetname = "dummy";
                //     _prop.asset = "prop_0001";
                // }
                // proplist.Add(_prop);

                // iFoundIndex = proplist.Count;
                //__construct($_name, $_id, $_pos, $_asset, $_assetname)
                $_prop = new PropSet($lKV[$iCandidateIndex][0], $iCandidateIndex, $lKV[$iCandidateIndex][1], "dummyfile", "dummy_assetname");
                array_push($proplist, $_prop);
                
                $iFoundIndex = count($proplist) - 1;
            }
        }

        return $iFoundIndex;
        #endregion
    }

    /// <summary>
    /// 檢查是否在 setlist,  沒有的話就找出 ASSET 並加入
    /// </summary>
    /// <param name="setcandidate"></param>
    /// <param name="lKV"></param>
    /// <param name="setlist"></param>
    /// <param name="findPreference"></param>
    private function checkInSetListOrAdd($iCandidateIndex, &$lKV, &$setlist, $findPreference)
    {
        #region 檢查 set
        $bfind = false;
        $iFoundIndex = -1;
        //foreach ($iCandidateIndex in setcandidate)
        {
            for ($i = 0; $i < count($setlist); $i++)
            {
                if ($setlist[$i]->name == $lKV[$iCandidateIndex][0])
                {
                    //find!
                    $bfind = true;
                    $iFoundIndex = $i;
                    break;
                }
            }

            if ($bfind == false)
            {
                //find char asset, add to charlist
/*                fofScene.Init(findPreference, lKV[iCandidateIndex].Key, AssetType.Scene);
                fofScene.find();

                PropSet _set = new PropSet();
                _set.id = iCandidateIndex;
                _set.name = lKV[iCandidateIndex].Key;
                _set.pos = lKV[iCandidateIndex].Value;

                if (fofScene.GetResult() != null)
                {
                    Dictionary<string, string> tmp = fofScene.GetResult();
                    _set.assetname = tmp["name"];
                    _set.name = tmp["name"];
                    _set.asset = tmp["filename"];
                }
                else
                {
                    //_char.asset = "no_matched_asset_file";
                    _set.assetname = "dummy";
                    _set.name = "dummy";
                    _set.asset = "set_0001";
                }
*/
                // __construct($_name, $_id, $_pos, $_asset, $_assetname)
                $_set = new PropSet($lKV[$iCandidateIndex][0], $iCandidateIndex, $lKV[$iCandidateIndex][1], "dummyfile", "dummy_assetname");
                array_push($setlist, $_set);
                
                $iFoundIndex = count($setlist);
            }
        }

        return $iFoundIndex;
        #endregion
    }
}

// public class FileDescriptor
// {
//     string Filename;

//     public FileDescriptor(string _filename)
//     {
//         Filename = _filename;
//     }
// }



#region declaration of datastructure
/// <summary>
/// 動畫幕
/// </summary>
class Scene
{
    public $chars = [];
    public $props = [];
    public $sets = [];
    public $timelight = "";

/*    public Scene()
    {
        chars = new List<Char>();
        props = new List<PropSet>();
        sets = new List<PropSet>();
    }
*/
    public function toJson()
    {
        return json_encode($this);// JsonConvert.SerializeObject(this, Formatting.Indented);
        /*StringBuilder sb = new StringBuilder();
        bool bfirst;

        if (timelight != "")
        {
            sb.Append("{\"timelight\": \"" + timelight + "\",");
        }

        sb.Append("{\"char\": {");
        bfirst = true;
        foreach (Char c in chars)
        {
            if (bfirst == true)
            {
                bfirst = false;
            }
            else
            {
                sb.Append(",");
            }
            sb.Append(c.toJson());
        }
        sb.Append("},");

        sb.Append("\"sets\": {");
        bfirst = true;
        foreach (PropSet ps in sets)
        {
            if (bfirst == true)
            {
                bfirst = false;
            }
            else
            {
                sb.Append(",");
            }
            sb.Append(ps.toJson());
        }
        sb.Append("},");

        sb.Append("\"props\": {");
        bfirst = true;
        foreach (PropSet ps in props)
        {
            if (bfirst == true)
            {
                bfirst = false;
            }
            else
            {
                sb.Append(",");
            }
            sb.Append(ps.toJson());
        }
        sb.Append("}}");

        return sb.ToString();*/
        
    }
}

class GeneralObject
{
    public $name;
    public $id;
    public $asset;
    public $assetname;
    public $pos;
}

/// <summary>
/// 腳色
/// </summary>
class Char extends GeneralObject
{
    // public $name;
    // public $id;
    // public $asset;
    // public $assetname;
    // public $pos;
    public $actions = [];
    public $adjunct_descriptions = [];

    public function __construct($_name, $_id, $_asset, $_assetname, $_pos, $_action_list, $_adjunct_descriptions)
    {
        $this->name = $_name;
        $this->id = $_id;
        $this->asset = $_asset;
        $this->assetname = $_assetname;
        $this->pos = $_pos;
        $this->actions = $_action_list;
        $this->adjunct_descriptions = $_adjunct_descriptions;
    }

    public function toJson()
    {
        return json_encode($this);
        // StringBuilder sb = new StringBuilder();
        // sb.Append("\"" + name + "\"");
        // sb.Append(":{");
        // sb.Append("\"id\":");
        // sb.Append(id.ToString() + ",");

        // sb.Append("\"asset\":");
        // sb.Append("\"" + asset + "\",");

        // //action
        // sb.Append("\"action\":{");
        // bool bFirst = true;
        // foreach (CharAction ca in actions)
        // {
        //     if (bFirst == false)
        //     {
        //         sb.Append(",");
        //     }
        //     else
        //     {
        //         bFirst = false;
        //     }
        //     sb.Append(ca.toJson());
        // }

        // sb.Append("}");

        // sb.Append("}");

        // return sb.ToString();
    }
}


/// <summary>
/// 場景、道具共用 struct
/// </summary>
class PropSet extends GeneralObject
{
    // public $name;
    // public $id;
    // public $pos;
    // public $asset;
    // public $assetname;

    public function __construct($_name, $_id, $_pos, $_asset, $_assetname)
    {
        $this->name = $_name;
        $this->id = $_id;
        $this->pos = $_pos;
        $this->asset = $_asset;
        $this->assetname = $_assetname;
    }

    /*public function PropSet($_name, $_id, $_pos, $_asset, $_assetname)
    {
        $this->name = $_name;
        $this->id = $_id;
        $this->pos = $_pos;
        $this->asset = $_asset;
        $this->assetname = $_assetname;
    }*/

    public function toJson()
    {
        $sb = "";
        $sb .= "\"" . $this->name ."\"";
        $sb .= ":{";
        $sb .= "\"id\":";
        $sb .= $this->id . ",";

        $sb .= "\"asset\":";
        $sb .= "\"" . $this->asset . "\"";
        $sb .= "}";

        return $sb;
    }
}

/// <summary>
/// 腳色動作
/// </summary>
class CharAction extends GeneralObject
{
    // public $name;
    public $target;
    // public $pos;
    // public $asset;
    // public $assetname;

    public function __construct($_name, $_target, $_id, $_pos, $_asset, $_assetname)
    {
        $this->name = $_name;
        $this->target = $_target;
        $this->pos = $_pos;
        $this->asset = $_asset;
        $this->assetname = $_assetname;
        $this->id = $_id;
    }

    /*public function CharAction($_name, $_target, $_pos, $_asset, $_assetname)
    {
        $this->name = $_name;
        $this->target = $_target;
        $this->pos = $_pos;
        $this->asset = $_asset;
        $this->assetname = $_assetname;
    }*/

    public function toJson()
    {
        $sb = "";
        $sb .= "\"" . $this->name ."\"";
        $sb .= ":{";
        $sb .= "\"target\":";
        $sb .= $this->target . ",";

        $sb .= "\"asset\":";
        $sb .= "\"" . $this->asset . "\"";
        $sb .= "}";

        return $sb;
    }
}

///// <summary>
///// 道具
///// </summary>
//public struct Prop
//{
//    string name;
//    $id;
//    string asset;

//    public Prop(string _name, $_id, string _asset)
//    {
//        name = _name;
//        id = _id;
//        asset = _asset;
//    }
//}

///// <summary>
///// 場景
///// </summary>
//public struct Set
//{
//    string name;
//    $id;
//    string asset;

//    public Set(string _name, $_id, string _asset)
//    {
//        name = _name;
//        id = _id;
//        asset = _asset;
//    }
//}








#endregion

