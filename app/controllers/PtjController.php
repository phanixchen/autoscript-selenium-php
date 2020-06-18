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
        
        // if (!$this->request->isPost()) {
        //     return $this->http405();
        // }
        
        // // Check parameters
        // if (isset($_POST["story"]) == false || isset($_POST["chars"]) == false || isset($_POST["props"]) == false || isset($_POST["sets"]) == false)
        // {
        //     return $this->http400("Parameter missing");
        // }

        // ControlTerm::InitPreTerms($_POST["chars"], $_POST["props"], $_POST["sets"]);
        // $input = ControlTerm::checkInControlTerms($_POST["story"]);

        // $ckip_tagger = new Ckiptagger();
        // $ret = $ckip_tagger->postagging($input);
        $arr_ctrlchars = explode(",", $_POST["chars"]);
        $arr_ctrlprops = explode(",", $_POST["props"]);
        $arr_ctrlsets = explode(",", $_POST["sets"]);

        $ret = '"(FW)　小鈴(Nb)　"(FW)　、(PAUSECATEGORY)　"(FW)　風太(Na)　"(FW)　、(PAUSECATEGORY)　"(FW)　美穗(Nb)　"(FW)　和(Caa)　另外(Da)　一(Neu)　位(Nf)　同學(Na)　一起(D)　走(VA)　在(P)　校園(Nc)　裡(Ncd)　。(PERIODCATEGORY)　
"(FW)　風(Na)　太(Dfa)　"(FW)　遞給(VD)　"(FW)　小鈴(Nb)　"(FW)　一(Neu)　本(Nf)　書(Na)　。(PERIODCATEGORY)　';
var_dump($ret);
echo "\r\n\r\n";
        

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
        


        return;
    }

    public function testindexAction()
    {
        $this->view->disable();
        $input = [];
        $input[0] = "#1:1.[0] S(theme:NP(DUMMY1:Nba(DUMMY1:Nba(DUMMY1:Nba:小鈴|Head:Caa:、|DUMMY2:Nba:風太)|Head:Caa:、|DUMMY2:Nba:美穗)|Head:Caa:和|DUMMY2:NP(quantity:Daa:另外|quantifier:DM:一位|Head:Nab:同學))|manner:Dh:一起|Head:VA11:走|location:PP(Head:P21:在|DUMMY:NP(property:Ncb:校園|Head:Ncda:裡)))#。(PERIODCATEGORY)";
        $input[1] = "#2:1.[0] S(agent:NP(Head:Nba:風太)|Head:VD1:遞給|goal:NP(Head:Nba:小鈴)|theme:NP(quantifier:DM:一本|Head:Nab:書))#。(PERIODCATEGORY)";

        $pa = new ParseresultTojson();

        $l_lkv[0] = $pa->ptoj($input[0]);
        $l_lkv[1] = $pa->ptoj($input[1]);

        var_dump($l_lkv);
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