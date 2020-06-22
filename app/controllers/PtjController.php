<?php

class PtjController extends ControllerBase
{
    public function testAction()
    {
        $this->view->disable();
        $tt = new Ckiptagger();
        $data = $tt->postagging("1234\r\n");

        echo $data;
    }

    // public function testAction()
    // {
    //     $inputstring = '"(FW)　風太(Na)　"(FW)　遞給(VD)　"(FW)　小鈴(Nb)　"(FW)　一(Neu)　本(Nf)　書(Na)　。(PERIODCATEGORY)　';
    //     $repstring = "";
    //     echo preg_replace('pattern', replacement, $inputstring);
    // }

    public function indexAction()
    {
        $this->view->disable();
        
        if (!$this->request->isPost()) {
            return $this->http405();
        }
        
        // Check parameters
        if (isset($_POST["story"]) == false || isset($_POST["chars"]) == false || isset($_POST["props"]) == false || isset($_POST["sets"]) == false)
        {
            return $this->http400("Parameter missing");
        }

        ControlTerm::InitPreTerms($_POST["chars"], $_POST["props"], $_POST["sets"]);
        $input = ControlTerm::checkInControlTerms($_POST["story"]);

        $ckip_tagger = new Ckiptagger();
        $ret = $ckip_tagger->postagging($input);


        $arr_ctrlchars = explode(",", $_POST["chars"]);
        $arr_ctrlprops = explode(",", $_POST["props"]);
        $arr_ctrlsets = explode(",", $_POST["sets"]);

//         $ret = '"(FW)　小鈴(Nb)　"(FW)　、(PAUSECATEGORY)　"(FW)　風太(Na)　"(FW)　、(PAUSECATEGORY)　"(FW)　美穗(Nb)　"(FW)　和(Caa)　另外(Da)　一(Neu)　位(Nf)　同學(Na)　一起(D)　走(VA)　在(P)　校園(Nc)　裡(Ncd)　。(PERIODCATEGORY)　
// "(FW)　風(Na)　太(Dfa)　"(FW)　遞給(VD)　"(FW)　小鈴(Nb)　"(FW)　一(Neu)　本(Nf)　書(Na)　。(PERIODCATEGORY)　';
// var_dump($ret);
// echo "\r\n\r\n";
        

        $istart = 0;
        $inext = 0;
        $tmparr = explode("　", $ret);
        // var_dump($tmparr);
        $lengtharr = count($tmparr);

        // search and replace control terms
        while ($istart < $lengtharr)
        {
            if (trim($tmparr[$istart]) != '"(FW)')
            {
                $istart++;
                continue;
            }
            else
            {
                for ($inext = $istart+1; $inext<$lengtharr; $inext++)
                {
                    if (trim($tmparr[$inext]) == '"(FW)')
                    {
                        break;
                    }
                }

                if (trim($tmparr[$inext]) != '"(FW)')
                {
                    //not found the next FW breaker
                    break;
                }
                else
                {
                    $strcandidatectrlterm = "";
                    for($itmp = $istart+1; $itmp < $inext; $itmp++)
                    {
                        $explodearr = explode("(", $tmparr[$itmp]);
                        $strcandidatectrlterm .= $explodearr[0];
                    }

                    // replace control terms
                    if (in_array($strcandidatectrlterm, $arr_ctrlchars))
                    {
                        for($itmp = $istart+1; $itmp < $inext; $itmp++)
                            unset($tmparr[$itmp]);

                        $tmparr[$istart+1] = $strcandidatectrlterm . "(Nb)";


                        if ($tmparr[$istart] == "\n\"(FW)")
                            $tmparr[$istart] = "\n";
                        else
                            unset($tmparr[$istart]);
                        if ($tmparr[$inext] == "\n\"(FW)")
                            $tmparr[$inext] = "\n";
                        else
                            unset($tmparr[$inext]);
                    }
                    if (in_array($strcandidatectrlterm, $arr_ctrlprops))
                    {
                        for($itmp = $istart+1; $itmp < $inext; $itmp++)
                            unset($tmparr[$itmp]);

                        $tmparr[$istart+1] = $strcandidatectrlterm . "(Na)";


                        if ($tmparr[$istart] == "\n\"(FW)")
                            $tmparr[$istart] = "\n";
                        else
                            unset($tmparr[$istart]);
                        if ($tmparr[$inext] == "\n\"(FW)")
                            $tmparr[$inext] = "\n";
                        else
                            unset($tmparr[$inext]);
                    }
                    if (in_array($strcandidatectrlterm, $arr_ctrlsets))
                    {
                        for($itmp = $istart+1; $itmp < $inext; $itmp++)
                            unset($tmparr[$itmp]);

                        $tmparr[$istart+1] = $strcandidatectrlterm . "(Nc)";


                        if ($tmparr[$istart] == "\n\"(FW)")
                            $tmparr[$istart] = "\n";
                        else
                            unset($tmparr[$istart]);
                        if ($tmparr[$inext] == "\n\"(FW)")
                            $tmparr[$inext] = "\n";
                        else
                            unset($tmparr[$inext]);
                    }


                    $istart = $inext+1;
                    ksort($tmparr); // resort by index
                }
            }
        }
        
        
        $newret = str_replace("　\n　", "　\n", implode("　", $tmparr));
// var_dump($newret);

        $test = new ParserSelenium();
        $arrret = $test->TheSeleniumTest($newret);


        $test->TeardownTest();

        var_dump($arrret);
        
        $jsonarr = $this->parsetojson($arrret);

        echo "\n\n\n\n\n";

        var_dump($jsonarr);


        return;
    }

    public function jsonparsingAction($input = null)
    {
        $this->view->disable();

        if ($input == null)
            $input = '{"id": 0, "data": {"role": null, "pos": "%", "word": null}, "children": [{"id": 1, "data": {"role": "predication", "pos": "S", "word": null}, "children": [{"id": 2, "data": {"role": "agent", "pos": "NP", "word": null}, "children": [{"id": 3, "data": {"role": "Head", "pos": "Nba", "word": "\u67ef\u666f\u9a30"}, "children": []}]}, {"id": 4, "data": {"role": "Head", "pos": "VC31", "word": "\u62ff"}, "children": []}, {"id": 5, "data": {"role": "aspect", "pos": "Di", "word": "\u8457"}, "children": []}, {"id": 6, "data": {"role": "theme", "pos": "NP", "word": null}, "children": [{"id": 7, "data": {"role": "Head", "pos": "Nab", "word": "\u81c9\u76c6"}, "children": []}]}, {"id": 8, "data": {"role": "complement", "pos": "VP", "word": null}, "children": [{"id": 9, "data": {"role": "Head", "pos": "VC1", "word": "\u7d93\u904e"}, "children": []}, {"id": 10, "data": {"role": "goal", "pos": "NP", "word": null}, "children": [{"id": 11, "data": {"role": "Head", "pos": "Nba", "word": "\""}, "children": []}]}, {"id": 12, "data": {"role": "goal", "pos": "NP", "word": null}, "children": [{"id": 13, "data": {"role": "property", "pos": "Ncb", "word": "\u5bbf\u820d"}, "children": []}, {"id": 14, "data": {"role": "property", "pos": "Nba", "word": "\"\""}, "children": []}, {"id": 15, "data": {"role": "property", "pos": "A", "word": "\u516c\u5171"}, "children": []}, {"id": 16, "data": {"role": "Head", "pos": "Nab", "word": "\u96fb\u8a71"}, "children": []}]}]}]}, {"id": 17, "data": {"role": "property", "pos": "Nba", "word": "\""}, "children": []}]}';

        $obj = json_decode($input);
        $obj = $obj->children;
        
        $idepth = 0;
        $i = 0;
        $arrS = [];
        $arrV = [];
        $arrO = [];
        $arrSet = [];
        $bHeadV = false;
        $_queue = [];
        foreach ($obj as $o)
            array_push($_queue, $o);

        while (count($_queue) > 0)
        {
            $o = array_shift($_queue);
            $this->tracetree($o, $idepth, $arrS, $arrV, $arrO, $arrSet, $bHeadV, $obj, $_queue);
        }
            
        
        var_dump($arrS);
    }

    public function tracetree(&$node, $idepth, &$arrS, &$arrV, &$arrO, &$arrSet, &$bHeadV, &$originobj, &$_queue)
    {
        if ($bHeadV == false)
        {
            // var_dump($node->data->role);
            if ($node->data->role == "Head")
            {

                if (substr($node->data->pos, 0, 1) == "V" && $node->data->word != null)
                {
                    // 
                    $bHeadV = true;
                    var_dump($node);
                    $array_push($arrV, $node);
                    $this->getS($originobj, $arrS, $node->id);
// var_dump($node);
                }
            }
        }
        else
        {

        }


        foreach ($node->children as $o)
            array_push($_queue, $o);
    }

    public function getS($originobj, &$arrS, $id)
    {
        foreach ($originobj as $o)
        {
            if ($o->id < $id && substr($o->data->pos, 0, 1) == "N" && $o->data->word != null)
            {
                echo "PUSH\n\n";
                if (isset($o->actions) == false)
                {
                    $o->actions = [];
                }
                array_push($o->actions, $id);
                array_push($arrS, $o);
            }
            if ($o->children != null)
            {
                $this->getS($o->children, $arrS, $id);
            }
        }
    }

    public function parsetestAction()
    {
//         $this->view->disable();
//         // var_dump($this->config->application->cacheDir);
//         // return;

//         $arr_ctrlchars = explode(",", $_POST["chars"]);
//         $arr_ctrlprops = explode(",", $_POST["props"]);
//         $arr_ctrlsets = explode(",", $_POST["sets"]);

//         $ret = '"(FW)　小鈴(Nb)　"(FW)　、(PAUSECATEGORY)　"(FW)　風太(Na)　"(FW)　、(PAUSECATEGORY)　"(FW)　美穗(Nb)　"(FW)　和(Caa)　另外(Da)　一(Neu)　位(Nf)　同學(Na)　一起(D)　走(VA)　在(P)　校園(Nc)　裡(Ncd)　。(PERIODCATEGORY)　
// "(FW)　風(Na)　太(Dfa)　"(FW)　遞給(VD)　"(FW)　小鈴(Nb)　"(FW)　一(Neu)　本(Nf)　書(Na)　。(PERIODCATEGORY)　';
// var_dump($ret);
// echo "\r\n\r\n";
        

//         $istart = 0;
//         $inext = 0;
//         $tmparr = explode("　", $ret);
//         // var_dump($tmparr);
//         $lengtharr = count($tmparr);

//         // search and replace control terms
//         while ($istart < $lengtharr)
//         {
//             if (trim($tmparr[$istart]) != '"(FW)')
//             {
//                 $istart++;
//                 continue;
//             }
//             else
//             {
//                 for ($inext = $istart+1; $inext<$lengtharr; $inext++)
//                 {
//                     if (trim($tmparr[$inext]) == '"(FW)')
//                     {
//                         break;
//                     }
//                 }

//                 if (trim($tmparr[$inext]) != '"(FW)')
//                 {
//                     //not found the next FW breaker
//                     break;
//                 }
//                 else
//                 {
//                     $strcandidatectrlterm = "";
//                     for($itmp = $istart+1; $itmp < $inext; $itmp++)
//                     {
//                         $explodearr = explode("(", $tmparr[$itmp]);
//                         $strcandidatectrlterm .= $explodearr[0];
//                     }

//                     // replace control terms
//                     if (in_array($strcandidatectrlterm, $arr_ctrlchars))
//                     {
//                         for($itmp = $istart+1; $itmp < $inext; $itmp++)
//                             unset($tmparr[$itmp]);

//                         $tmparr[$istart+1] = $strcandidatectrlterm . "(Nb)";


//                         if ($tmparr[$istart] == "\n\"(FW)")
//                             $tmparr[$istart] = "\n";
//                         else
//                             unset($tmparr[$istart]);
//                         if ($tmparr[$inext] == "\n\"(FW)")
//                             $tmparr[$inext] = "\n";
//                         else
//                             unset($tmparr[$inext]);
//                     }
//                     if (in_array($strcandidatectrlterm, $arr_ctrlprops))
//                     {
//                         for($itmp = $istart+1; $itmp < $inext; $itmp++)
//                             unset($tmparr[$itmp]);

//                         $tmparr[$istart+1] = $strcandidatectrlterm . "(Na)";


//                         if ($tmparr[$istart] == "\n\"(FW)")
//                             $tmparr[$istart] = "\n";
//                         else
//                             unset($tmparr[$istart]);
//                         if ($tmparr[$inext] == "\n\"(FW)")
//                             $tmparr[$inext] = "\n";
//                         else
//                             unset($tmparr[$inext]);
//                     }
//                     if (in_array($strcandidatectrlterm, $arr_ctrlsets))
//                     {
//                         for($itmp = $istart+1; $itmp < $inext; $itmp++)
//                             unset($tmparr[$itmp]);

//                         $tmparr[$istart+1] = $strcandidatectrlterm . "(Nc)";


//                         if ($tmparr[$istart] == "\n\"(FW)")
//                             $tmparr[$istart] = "\n";
//                         else
//                             unset($tmparr[$istart]);
//                         if ($tmparr[$inext] == "\n\"(FW)")
//                             $tmparr[$inext] = "\n";
//                         else
//                             unset($tmparr[$inext]);
//                     }


//                     $istart = $inext+1;
//                     ksort($tmparr); // resort by index
//                 }
//             }
//         }
        
        
//         $newret = str_replace("　\n　", "　\n", implode("　", $tmparr));

//         $ch = curl_init();
//         curl_setopt($ch, CURLOPT_URL, 'http://parser.iis.sinica.edu.tw/');
//         curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0');
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: parser.iis.sinica.edu.tw'));
//         curl_setopt($ch, CURLOPT_REFERER, 'http://parser.iis.sinica.edu.tw/');
//         curl_setopt($ch, CURLOPT_COOKIESESSION, true );
//         curl_setopt($ch, CURLOPT_COOKIEJAR, $this->config->application->cacheDir . 'cookie.txt');
//         curl_setopt($ch, CURLOPT_COOKIEFILE, $this->config->application->cacheDir . 'cookie.txt');
//         $html = curl_exec($ch);
//         var_dump($html);

//         $i = strpos($html, 'name="id" value="')+ 17;
//         $j = strpos($html, "\"", strpos($html, 'name="id" value="')+ 18);
//         // var_dump($i, $j);
//         $strtmp = substr($html, strpos($html, 'name="id" value="')+ 17, $j-$i);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("myTag"=>$newret, "bTag"=>"1", "id" => $strtmp))); 
//         curl_setopt($ch, CURLOPT_URL, 'http://parser.iis.sinica.edu.tw/webparser.asp');
//         curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0');
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: parser.iis.sinica.edu.tw'));
//         curl_setopt($ch, CURLOPT_REFERER, 'http://parser.iis.sinica.edu.tw/');
//         curl_setopt($ch, CURLOPT_COOKIESESSION, true );
//         curl_setopt($ch, CURLOPT_COOKIEJAR, $this->config->application->cacheDir . 'cookie.txt');
//         curl_setopt($ch, CURLOPT_COOKIEFILE, $this->config->application->cacheDir . 'cookie.txt');
//         $html = curl_exec($ch);

//         var_dump($html);
    }

    public function testindexAction()
    {
        $this->view->disable();
        $input = [];
        $input[0] = "#1:1.[0] S(theme:NP(DUMMY1:Nba(DUMMY1:Nba(DUMMY1:Nba:小鈴|Head:Caa:、|DUMMY2:Nba:風太)|Head:Caa:、|DUMMY2:Nba:美穗)|Head:Caa:和|DUMMY2:NP(quantity:Daa:另外|quantifier:DM:一位|Head:Nab:同學))|manner:Dh:一起|Head:VA11:走|location:PP(Head:P21:在|DUMMY:NP(property:Ncb:校園|Head:Ncda:裡)))#。(PERIODCATEGORY)";
        $input[1] = "#2:1.[0] S(agent:NP(Head:Nba:風太)|Head:VD1:遞給|goal:NP(Head:Nba:小鈴)|theme:NP(quantifier:DM:一本|Head:Nab:書))#。(PERIODCATEGORY)";
        // $input[0] = "S(theme:NP(DUMMY1:Nba(DUMMY1:Nba(DUMMY1:Nba:小鈴|Head:Caa:、|DUMMY2:Nba:風太)|Head:Caa:、|DUMMY2:Nba:美穗)|Head:Caa:和|DUMMY2:NP(quantity:Daa:另外|quantifier:DM:一位|Head:Nab:同學))|manner:Dh:一起|Head:VA11:走|location:PP(Head:P21:在|DUMMY:NP(property:Ncb:校園|Head:Ncda:裡)))#";
        // $input[1] = "S(agent:NP(Head:Nba:風太)|Head:VD1:遞給|goal:NP(Head:Nba:小鈴)|theme:NP(quantifier:DM:一本|Head:Nab:書))#";

        $input[0] = "#1:1.[0] S(agent:NP(Head:Nba:柯景騰)|Head:VC31:拿|aspect:Di:著|theme:NP(Head:Nab:臉盆)|complement:VP(Head:VC1:經過|goal:NP(property:Ncb:宿舍|property:A:公共|Head:Nab:電話)))#，(COMMACATEGORY)";
        $input[1] = "#2:1.[0] VP(manner:VH11:若有所思|Head:VC2:看|aspect:Di:了|goal:NP(predication:V‧的(head:VA4:排隊|Head:DE:的)|possessor:Nab:人|Head:Nab:龍|quantifier:DM:一眼))#，(COMMACATEGORY)";
        $input[2] = "#3:1.[0] VP(manner:V‧地(head:VH11:落寞|Head:DE:地)|Head:VC1:走過)#。(PERIODCATEGORY)";

        var_dump($input);
        $tmp = $this->parsetojson($input);

        echo "\n\n\n\n\n";
        var_dump($tmp);
    }

    public function parsetojson($input)
    {
        $l_lkv = [];

        $pa = new ParseresultTojson();

        for( $i = 0; $i < count($input); $i++)
        {
            $input[$i] = substr($input[$i], strpos($input[$i], "] S") + 2, strlen($input[$i]) - strpos(strrev($input[$i]), "#") - strpos($input[$i], "] S") - 2);

            $l_lkv[$i] = $pa->ptoj($input[$i]);
        }
        return $l_lkv;
    }

    public function oldindexAction()
    {
    	$this->view->disable();
        
        if (!$this->request->isPost()) {
            return $this->http405();
        }
        
        // Check parameters
        if (isset($_POST["story"]) == false || isset($_POST["chars"]) == false || isset($_POST["props"]) == false || isset($_POST["sets"]) == false)
        {
            return $this->http400("Parameter missing");
        }

        ControlTerm::InitPreTerms($_POST["chars"], $_POST["props"], $_POST["sets"]);
        $input = ControlTerm::checkInControlTerms($_POST["story"]);

        // echo $input . "\r\n";

        // $test = new CkipSelenium();
        // $ret = $test->TheSeleniumTest($input);

//         $ret = "艾瑪(Nb)　從(P)　\"(FW)　咖啡廳(Nc)　\"(FW)　走出(VCL)　來到(VCL)　馬路(Na)　上(Ncd)　。(PERIODCATEGORY)\r\n
// ----------------------------------------------------------------------------------------------------------------------------------\r\n
// \r\n
// ";

        // echo $ret . "\r\n\r\n";

        // $test->TeardownTest();
        $ckip_tagger = new Ckiptagger();
        $ret = $ckip_tagger->postagging($input);

        $pa = new PosAnalysis();

        // from POS tagging result to term->POS mapping array
        $l_lkv = $pa->posConvertToJsonObj(trim($ret));
        $lJson = [];

        for ($i = 0; $i < count($l_lkv); $i++)
        {
            ControlTerm::postProcess($l_lkv[$i]);

            // merge controller term
            $lKV = $l_lkv[$i];
            for ($j = count($lKV) - 1; $j >=0 ; $j--)
            {
                switch (strtoupper($lKV[$j][1]))
                {
                    case "FW":
                        if ($lKV[$j][0] == "\"")
                        {
                            for ($itmp = $j-1; $itmp >= 0; $itmp-- )
                            {
                                if ($lKV[$itmp][0] == "\"")
                                {
                                    $str_newterm = "";
                                    for ($iconcat = $itmp+1;$iconcat < $j; $iconcat++)
                                    {
                                        $str_newterm .= $lKV[$iconcat][0];
                                    }
                                    if (ControlTerm::_inCtrlTerms($str_newterm, "char"))
                                    {
                                        $lKV[$itmp+1][0] = $str_newterm;
                                        $lKV[$itmp+1][1] = "NB";

                                        for ($iconcat = $itmp+2;$iconcat < $j; $iconcat++)
                                        {
                                            unset($lKV[$iconcat]);
                                        }
                                    }
                                    else if (ControlTerm::_inCtrlTerms($str_newterm, "props"))
                                    {
                                        $lKV[$itmp+1][0] = $str_newterm;
                                        $lKV[$itmp+1][1] = "NA";

                                        for ($iconcat = $itmp+2;$iconcat < $j; $iconcat++)
                                        {
                                            unset($lKV[$iconcat]);
                                        }
                                    }
                                    else if (ControlTerm::_inCtrlTerms($str_newterm, "sets"))
                                    {
                                        $lKV[$itmp+1][0] = $str_newterm;
                                        $lKV[$itmp+1][1] = "NC";

                                        for ($iconcat = $itmp+2;$iconcat < $j; $iconcat++)
                                        {
                                            unset($lKV[$iconcat]);
                                        }
                                    }

                                    $j = $itmp;
                                    break;
                                }
                            }
                        }
                        break;
                }
            }
            $l_lkv[$i] = array_values($lKV);

            // var_dump($l_lkv[$i]);

            array_push($lJson, json_decode($pa->cKVlistToJson_v2($l_lkv[$i], FindModelFilePref::YesAndSimilar, DbMode::Remote)));
        }

        // var_dump(json_encode($lJson));

        $resp = new \Phalcon\Http\Response();
        $resp->setStatusCode(200, "OK");
        $resp->setHeader("Content-Type", "application/json");
        $resp->setHeader("Access-Control-Allow-Origin", "*");
        $resp->setHeader('Access-Control-Allow-Headers', 'X-Requested-With');
        $resp->sendHeaders();
        // $resp->setContent(json_encode($lJson, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT));
        $resp->setContent(json_encode($lJson));
        $resp->send();
        return;
    }

}


// class FindModelFilePref
// {
//     const No = 1;
//     const Yes = 2;
//     const YesAndSimilar = 3;
// }