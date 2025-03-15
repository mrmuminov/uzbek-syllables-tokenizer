<?php

namespace MrMuminov\UzSyllablesTokenizer;


class Tokenizer
{

    public function tokenize($original_word)
    {
        // qo'sh harfarni almashtirish
        $word = str_replace("sh", '@', $original_word);
        $word = str_replace("ch", '#', $word);
        $word = str_replace("ng", '%', $word);
        $word = str_replace("g'", '&', $word);
        $word = str_replace("o'", '$', $word);

        $token = preg_replace('/[bdfghjklmnpqrstvxyz@#%&\']/iu', 'C', $word);
        $token = preg_replace('/[aeiou\$]/iu', 'V', $token);
        $token = preg_replace('/\'/iu', 'T', $token);
        $token = $this->searchTokens($token);
        $result = "";
        $pos = 0;
        for ($i = 0; $i < strlen($token); $i++) {
            if ($token[$i] != '-') {
                $result .= $word[$pos];
                $pos++;
            } else {
                $result .= "-";
            }
        }
        $result = preg_replace('/@/u', 'sh', $result);
        $result = preg_replace('/#/u', 'ch', $result);
        $result = preg_replace('/%/iu', 'ng', $result);
        $result = preg_replace('/&/iu', "g'", $result);
        $result = preg_replace('/\$/iu', "o'", $result);

        return explode("-", $result);
    }


    function searchTokens($token)
    {
        $patterns = array("CCVCC", "CCVC", "CVCC", "CVC", "CCV", "VCC", "CVT", "CV", "VC", "VT", "V");
        foreach ($patterns as $p) {
            if (mb_strpos($token, $p) === 0) {
                $next = mb_substr($token, mb_strlen($p));
                if ($next === "") {
                    return $p;
                }

                $matchNext = $p === "CV" && (mb_strpos($next, "VCC") === 0 || mb_strpos($next, "VC") === 0 || mb_strpos($next, "V") === 0);
                $matchNext = $matchNext || mb_strpos($next, "CVCC") === 0 || mb_strpos($next, "CVC") === 0 || mb_strpos($next, "CV") === 0;
                if ($matchNext) {
                    return $p . "-" . $this->searchTokens($next);
                }
            }
        }
        return $token;
    }
}
