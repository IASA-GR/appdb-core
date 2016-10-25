<?php
/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */

// OBSOLETE PAGING GLOBAL FUNCTIONS
function printPagingButton($parent,$page) {
        $disabled = "";
        if ($page == "prev") {
                $page = "&lt;";
                $func = 'prevPage();';
                if ($parent->currentPage == 0) $disabled = 'disabled="disabled"';
        } elseif ($page == "next") {
                $page = "&gt;";
                $func = 'nextPage();';
                if ($parent->currentPage+1 == $parent->pageCount) $disabled = 'disabled="disabled"';
        } else {
                $func = 'gotoPage('.$page.');';
                if ($parent->currentPage+1 == $page) $disabled = 'disabled="disabled"';
        }
        echo '<button dojoType="dijit.form.Button" style="font-family:Arial,sans-serif;font-size:12px;font-weight:400;color:#454545;" '.$disabled.' onclick="'.$func.'">'.$page.'</button>';
}

function putPaging($parent) {
        echo "<div style='margin-left: auto; margin-right: auto; display:block; width:auto !important; max-width:500px;  overflow-x:auto;'>";
        echo "<div style='margin-left: auto; margin-right: auto; display:block; width:".(($parent->pageCount+2)*38)."px; '>";
        printPagingButton($parent,"prev");
        for ($i=1;$i<=$parent->pageCount;$i++) {
                printPagingButton($parent,$i);
        }
        printPagingButton($parent,"next");
        echo "</div>";
        echo "</div>";
}
function putPaging2($parent) {
        if ($parent->total>0) {
                printPagingButton($parent,"prev");
                if ($parent->pageCount <= 7) {
                        for ($i=1;$i<=$parent->pageCount;$i++) {
                                printPagingButton($parent,$i);
                                echo "    ";
                        }
                } else {
                        for ($i=1;$i<=2;$i++) {
                                printPagingButton($parent,$i);
                                echo "     ";
                        }; echo "...";
                        $j=1;
                        for ($i=floor($parent->pageCount/4);$i<$parent->pageCount-1;$i=floor($parent->pageCount/4)*$j) {
                                printPagingButton($parent,$i);
                                echo "     ";
                                $j++;
                        }; echo "...";
                        for ($i=$parent->pageCount-1;$i<=$parent->pageCount;$i++) {
                                printPagingButton($parent,$i);
                                echo "     ";
                        }
                }
                printPagingButton($parent,"next");
        }
}
function putPager($parent,$sides=2,$center=5){
        $right = $left = $sides;
        $sepleft = $sepright = "";
        $sep = "...";
        //no data
        if ($parent->pageCount<=1){
            return;
        }
        //not enough data
        if($parent->pageCount<=(($sides*2)+$center)){
            putPaging($parent);
            return;
        }
        echo "<div style='margin-left: auto; margin-right: auto; display:block;width:auto !important; max-width:500px; '>";
        echo "<div style='margin-left: auto; margin-right: auto; display:block;'>";
        printPagingButton($parent,"prev");
        if($parent->currentPage<($sides+$center-1)){
            $left = $sides+$center;
            $sepright=$sep;
            $center = 0;
        }else if(($parent->currentPage+$sides+$center-1)>=$parent->pageCount){
            $right=$sides+$center;
            $sepleft = $sep;
            $center = 0;
        }else{
            $sepright = $sepleft = $sep;
        }
        //left side buttons
        for($i=1;$i<=$left;$i++){
                printPagingButton($parent, $i);
        }
        echo $sepleft;
        //center buttons
        if($center>0){
            for ($i=($parent->currentPage-floor($center/2)+1);$i<=($parent->currentPage+floor($center/2)+1);$i++) {
                printPagingButton($parent,$i);
            }
        }
        echo $sepright;
        //right side buttons
        for ($i=($parent->pageCount-$right+1);$i<=$parent->pageCount;$i++) {
            printPagingButton($parent,$i);
        }
        printPagingButton($parent,"next");
        echo "</div>";
        echo "</div>";
}
