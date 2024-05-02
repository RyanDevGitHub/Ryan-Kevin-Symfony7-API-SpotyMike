<?php


namespace App\Service;


class PageUtils{
    public function checkPagination(int $currentPage, int $totalData, int $limite){
        

        // Calculate total number of pages
        $maxPage = ceil($totalData / $pageSize);
        if ($currentPage > 1 || $currentPage < $maxPage) {
            return null;
        }
        $offset = ($currentPage - 1) * $limite;

        // Calculate total number of pages
        $maxPage = ceil($totalData / $limite);
        return [$offset, $maxPage];
    }

    public function sendPaginationError(){
        return [
            'error' => true,
            'message'=> 'le parametres de paginations est invalide veillez fournir un numero de page valide '
        ];
    }
}