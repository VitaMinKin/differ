<?php

namespace Differ;

class DiffRenderer
{
    private $format;

    public function __construct($options = ['format' => 'text'])
    {
        $this->format = $options['format'];
    }

    public function render(array $diff)
    {
        if ($this->format == 'text') {
            return self::returnText($diff);
        }
    }

    private static function returnText(array $diff)
    {
        $funct = function($diff, $pre = "") use (&$funct) {
            $pre .= "   ";
            $result = array_reduce($diff, function ($acc, $elem) use (&$funct, $pre) {
                //$acc .= "{\n";

                $state = $elem['diff'];

                if (!empty($state)) {
                    if ($state['itemState'] != 'changed') {
                        if (is_array($state['value'])) {
                            $value = json_encode($state['value']);
                        } else {
                            $value = $state['value'];
                        }
                    }

                    switch ($state['itemState']) {
                        case 'unchanged':
                            $acc .= $pre."   {$elem['name']}: $value\n";
                        break;
                        case 'deleted':
                            $acc .= $pre." - {$elem['name']}: $value\n";
                        break;
                        case 'added':
                            $acc .= $pre." + {$elem['name']}: $value\n";
                        break;
                        case 'changed':
                            if (is_array($state['oldValue'])) {
                                $oldValue = json_encode($state['oldValue']);
                            } else {
                                $oldValue = $state['oldValue'];
                            }

                            if (is_array($state['newValue'])) {
                                $newValue = json_encode($state['newValue']);
                            } else {
                                $newValue = $state['newValue'];
                            }


                            $acc .= $pre." - {$elem['name']}: $oldValue\n";
                            $acc .= $pre." + {$elem['name']}: $newValue\n";
                        break;
                    }
                }

                if (!empty($elem['children'])) {
                    $acc .= $pre."{$elem['name']}: {\n";
                    $acc .= $funct($elem['children']);
                    $acc .= $pre."}\n";
                }

                return $acc;
            }, '');
            return $result;
        };

        return "{\n".$funct($diff)."}";
    }
}
