<?php


namespace App\Http\Controllers\Api\v1\TranscriptionProviders;


class GoogleCaptionGenerator
{
    protected $maxLinePerSubtitle = null;
    protected $minDuration = null;
    protected $maxDuration = null;
    protected $maxCharsPerLine = null;
    protected $maxCharPerSec = null;
    protected $minFrameGap =null;

    public function __construct()
    {
        /*$this->maxLinePerSubtitle = 3;
        $this->minDuration = 1 * 1000;
        $this->maxDuration = 7 * 1000;
        $this->maxCharsPerLine = 42;
        $this->maxCharPerSec = 25;
        $this->minFrameGap = 1;*/
    }

    public function setGuideline($guideLine)
    {
        $this->maxLinePerSubtitle = $guideLine['maxLinePerSubtitle'];
        $this->minDuration = $guideLine['minDuration'] * 1000;
        $this->maxDuration = $guideLine['maxDuration'] * 1000;
        $this->maxCharsPerLine = $guideLine['maxCharsPerLine'];
        $this->maxCharPerSec = $guideLine['maxCharsPerSecond'];
        $this->minFrameGap = $guideLine['frameGap'];
    }

    public function getMilliseconds($time)
    {
        return (rtrim($time, "s") * 1000);
    }

    public function getDiff($start, $end)
    {
        return ($this->getMilliseconds($end) - $this->getMilliseconds($start));
    }

    public function getWordDuration($word)
    {
        return ($this->getMilliseconds($word['endTime']) - $this->getMilliseconds($word['startTime']));
    }

    public function getDuration($currSubtitle, $word)
    {
        if ($currSubtitle['startTime'] !== null) {
            return ($this->getMilliseconds($word['endTime']) - $this->getMilliseconds($currSubtitle['startTime']));
        } else {
            return ($this->getMilliseconds($word['endTime']) - $this->getMilliseconds($word['startTime']));
        }
        // return ($this->getMilliseconds($word['endTime']) - $this->getMilliseconds($word['startTime']));
    }

    public function show($param) {
        echo PHP_EOL;
        echo PHP_EOL;
        echo "<pre>";
        var_dump($param);
        echo PHP_EOL;
        echo PHP_EOL;
    }

    public function durationCheck($currSubtitle, $word)
    {
        // $this->show($currSubtitle);
        // $this->show($word);
        //echo $this->getDuration($word);*/
        //$newDuration = 0;
        /*if ($currSubtitle['duration'] !== 0) {
            $newDuration = $this->getDuration($currSubtitle, $word);
        } else {*/
        $newDuration = $this->getDuration($currSubtitle,$word);//$this->getDiff($currSubtitle['endTime'], $word['endTime']);//$this->getDuration($word);
        //}
        // echo $this->show($newDuration);
        /*die($newDuration);
        die($this->minDuration);
        die($newDuration);*/

        if ($newDuration <= $this->minDuration) {
            return true;
        } elseif ($newDuration <= $this->maxDuration) {
            return true;
        } else {
            return false;
        }
        /*if (!empty($currSubtitle['duration']) && ($currSubtitle['duration'] + $this->getDuration($word)) > $this->minDuration
            && ($currSubtitle['duration'] + $this->getDuration($word)) < $this->maxDuration) {
            return true;
        } else {
            return false;
        }*/
    }

    public function cpsCheck($currSubtitle, $word)
    {
        //print_r($currSubtitle);
        //print_r($word);
        //if (!empty($currSubtitle['duration'])) {
        $duration = $this->getDuration($currSubtitle, $word);
        if ($duration < $this->minDuration) {
            return true;
        }
        $maxCharThisSub = ($currSubtitle['duration'] + $duration) * ($this->maxCharPerSec/1000);
        // echo 'maxchar ' . $maxCharThisSub;
        //character count check
        $addedWordLength = strlen($currSubtitle['subtitle'] . ' ' . $word['word']);
        //echo $currSubtitle['subtitle'] . ' ' . $word['word'];
        //echo $addedWordLength;
        if ($addedWordLength <= $maxCharThisSub) {
            return true;
        } else {
            return false;
        }
        //}
    }

    public function cplCheck($line, $word)
    {
        return (strlen($line . ' ' . $word['word']) <= $this->maxCharsPerLine);
        /*$newLength = strlen($line . ' ' . $word['word']);
        if ($newLength <= $this->maxCharsPerLine) {
            return true;
        }*/

        /*
        $subtitleLen = strlen($currSubtitle['subtitle']);
        //if ($subtitleLen <= $)
        $lineBreakCount = substr_count($currSubtitle['subtitle'], PHP_EOL);
        //line check
        if (($lineBreakCount + 1) <= $this->maxLinePerSubtitle) {
            $lines = explode(PHP_EOL, $currSubtitle['subtitle']);
            foreach($lines as $line) {
                $length = strlen($line) + strlen($word['word']);
                if ($length <= $this->maxCharsPerLine) {
                    return true;
                }
            }
        } else {
            return false;
        }*/
    }

    public function hardBreaker($word)
    {
        $lineBreakChars = array('.', '?');
        $lastchar = substr($word['word'], -1);
        /*var_dump($lastchar);
        die('s');*/
        if (in_array($lastchar, $lineBreakChars)) {
            return true;
        }

        return false;
    }

    public function generateCaption($param)
    {
        $finalCaption = array();
        //$subtitleIndex = 1;
        $currSubtitle = array();
        $currSubtitleDuration = 0;

        $currSubtitle['subtitle'] = '';
        $currSubtitle['duration'] = 0;
        $currSubtitle['endTime'] = null;
        $currSubtitle['startTime'] = null;

        foreach ($param['results'] as $result) {
            foreach ($result['alternatives'] as $alternative) {
                $hardBreak = false;
                $tempCaption = array();
                foreach ($alternative['words'] as $word) {
                    //die('sdd');
                    //var_dump($this->cpsCheck($currSubtitle, $word));
                    //die('f');
                    //if ($this->durationCheck($currSubtitle, $word)) {
                    if ($this->cpsCheck($currSubtitle, $word)) {
                        if ($this->durationCheck($currSubtitle, $word)) {
                            //die('dd');
                            if ($currSubtitle['startTime'] === null) {
                                $currSubtitle['startTime'] = $word['startTime'];
                            }

                            //check for CPL
                            $lines = explode(PHP_EOL, $currSubtitle['subtitle']);
                            $i = 1;
                            $addLine = false;
                            foreach($lines as $line) {
                                if (!$this->cplCheck($line, $word)) {
                                    if ($i < $this->maxLinePerSubtitle) {
                                        $addLine = true;
                                    } else {
                                        $addLine = false;
                                        $tempCaption = array(
                                            'sourceText' => trim($currSubtitle['subtitle']),
                                            'startTime' => $this->getMilliseconds($currSubtitle['startTime']),
                                            'endTime' => $this->getMilliseconds($currSubtitle['endTime'])
                                            //'diff' => $this->getDuration($currSubtitle, $currSubtitle)
                                        );
                                        // $this->show($tempCaption);
                                        //die('d');
                                        array_push($finalCaption, $tempCaption);
                                        // $this->show($tempCaption);
                                        $currSubtitle['subtitle'] = '';
                                        $currSubtitle['duration'] = 0;
                                        $currSubtitle['endTime'] = $word['endTime'];
                                        $currSubtitle['startTime'] = $word['startTime'];
                                    }
                                } else {
                                    $addLine = false;
                                }
                                $i++;
                            }

                            if ($addLine) {
                                $currSubtitle['subtitle'] = $currSubtitle['subtitle'] . PHP_EOL . $word['word'];
                            } else {
                                $currSubtitle['subtitle'] = $currSubtitle['subtitle'] . ' ' . $word['word'];
                            }
                            $currSubtitle['duration'] = $this->getDuration($currSubtitle, $word);//$currSubtitle['duration'] + $this->getDuration($word);
                            $currSubtitle['endTime'] = $word['endTime'];
                            if ($this->hardBreaker($word) === true) {
                                $tempCaption = array(
                                    'sourceText' => trim($currSubtitle['subtitle']),
                                    'startTime' => $this->getMilliseconds($currSubtitle['startTime']),
                                    'endTime' => $this->getMilliseconds($currSubtitle['endTime'])
                                    //'diff' => $this->getDuration($currSubtitle, $currSubtitle)
                                );
                                // $this->show($tempCaption);
                                //die('d');
                                array_push($finalCaption, $tempCaption);
                                // $this->show($tempCaption);
                                $currSubtitle['subtitle'] = '';
                                $currSubtitle['duration'] = 0;
                                $currSubtitle['endTime'] = null;
                                $currSubtitle['startTime'] = null;
                                //break;
                            }
                        } else {
                            // $this->show($currSubtitle);
                            //$this->show($word);
                            // die('d');
                            //echo "duration fails";
                            $tempCaption = array(
                                'sourceText' => trim($currSubtitle['subtitle']),
                                'startTime' => $this->getMilliseconds($currSubtitle['startTime']),
                                'endTime' => $this->getMilliseconds($currSubtitle['endTime'])
                                //'diff' => $this->getDuration($currSubtitle, $currSubtitle)
                            );
                            // $this->show($tempCaption);
                            //die('d');
                            array_push($finalCaption, $tempCaption);
                            // $this->show($tempCaption);
                            $currSubtitle['subtitle'] = $word['word'];
                            $currSubtitle['duration'] = $this->getWordDuration($word);
                            $currSubtitle['endTime'] = $word['endTime'];
                            $currSubtitle['startTime'] = $word['startTime'];
                        }
                        /*$tempCaption = array(
                            'subtitle' => $currSubtitle['subtitle'],
                            'startTime' => $currSubtitle['startTime'],
                            'endTime' => $currSubtitle['endTime']
                        );
                        array_push($finalCaption, $tempCaption);*/
                    } else {
                        $tempCaption = array(
                            'sourceText' => trim($currSubtitle['subtitle']),
                            'startTime' => $this->getMilliseconds($currSubtitle['startTime']),
                            'endTime' => $this->getMilliseconds($currSubtitle['endTime'])
                            //'diff' => $this->getDiff($currSubtitle['startTime'], $currSubtitle['endTime'])
                        );
                        array_push($finalCaption, $tempCaption);
                        //$this->show($tempCaption);
                        $currSubtitle['subtitle'] = $word['word'];
                        $currSubtitle['duration'] = $this->getWordDuration($word);
                        $currSubtitle['endTime'] = $word['endTime'];
                        $currSubtitle['startTime'] = $word['startTime'];
                       // echo "CPS check fails";
                    }
                    /*} else {
                        $tempCaption = array(
                            'subtitle' => $currSubtitle['subtitle'],
                            'startTime' => $currSubtitle['startTime'],
                            'endTime' => $currSubtitle['endTime']
                        );
                        array_push($finalCaption, $tempCaption);
                        $currSubtitle['subtitle'] = '';
                        $currSubtitle['duration'] = null;
                        $currSubtitle['endTime'] = null;
                        $currSubtitle['startTime'] = null;
                        echo "Duration check fails";
                    }*/
                }
            }
        }

        return $finalCaption;
        //echo "<pre>";
        //print_r($finalCaption);

    }
}
