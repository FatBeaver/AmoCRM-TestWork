<?php

class ApiWorker 
{   
    /**
     * Базовый путь API
     */
    const BASE_URL = 'https://dbistest.amocrm.ru';

    /**
     * Часть пути необходимая для авторизации.
     */
    const AUTH_PART_URL = 'USER_LOGIN=dbistest@test.com&USER_HASH=9e3ce3e9a8fe6ff15dcfcae67aa4c48a7c94c880';

    /**
     * Функция возвращающая массив, элементами которого являются сделки 
     * у которых присутствуют поле "компания" и/или поле "контакты".
     */
    public static function getLeads(string $url): array
    {
        $apiUrl = self::BASE_URL . $url . self::AUTH_PART_URL;

        $curl_session = curl_init($apiUrl);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $jsonData = curl_exec($curl_session);

        $data = json_decode($jsonData);
        $allLeads = $data->_embedded->items;

        return self::filterLeads($allLeads); 
    }


    private function getContacts(string $url): array
    {
        $apiContactsUrl = self::BASE_URL . $url . '&' . self::AUTH_PART_URL;

        $curl_session = curl_init($apiContactsUrl);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $jsonData = curl_exec($curl_session);

        $data = json_decode($jsonData);
        $contacts = $data->_embedded->items;

        return $contacts;
    }

    /**
     * Функция фильтрующая сделки.
     * Принимает все сделки полученные через API.
     * Возвращает сделки у которых присутствуют поле "компания" и/или поле "контакты"
     */
    private static function filterLeads(array $allLeads): array
    {   
        $filteredLeads = [];
        foreach($allLeads as $lead) {
            if ((!empty((array)$lead->company)) || (!empty((array)$lead->contacts))) {
                if (!empty((array)$lead->contacts)) {
                    $lead->contacts = self::getContacts($lead->contacts->_links->self->href);
                }
                $filteredLeads[] = $lead;
            }
        }

        return self::pureLeadsConstruct($filteredLeads);
    }

    /**
     * Создает массивы с ключами name, contacts, company и 
     * заполняет их данными из объектов сделок.
     */
    private static function pureLeadsConstruct(array $leads): array
    {   
        $pureLeads = [];
        foreach($leads as $lead) {
            $pureLead['name'] = $lead->name;
            $pureLead['contacts'] = '';
            if (!empty((array)$lead)) {
                foreach ($lead->contacts as $contact) {
                    $pureLead['contacts'] .= $contact->name;
                    $pureLead['contacts'] .= '  ';
                }
            }
            $pureLead['company'] = $lead->company->name;

            $pureLeads[] = $pureLead;
        }

        return $pureLeads;
    }
}