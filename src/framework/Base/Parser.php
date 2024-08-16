<?php

namespace Framework;

include "framework/framework.php";

use DiDom\Document;

class Parser
{
    private $page;
    private $pageEnd;

    public function __construct($page = 1, $pageEnd = 120)
    {
        $this->page = $page;
        $this->pageEnd = $pageEnd;
    }

    protected function parsePage($page)
    {
        $document = $this->getDocument("https://www.1c-bitrix.ru/partners/index_ajax.php?PAGEN_1={$page}");

        foreach ($document->find('a.bx-ui-tile__main-link') as $el) {
            $relativePath = trim($el->getAttribute('href'), './');
            $partnerPageUrl = "https://www.1c-bitrix.ru/partners/" . $relativePath;

            $partnerDocument = $this->getDocument($partnerPageUrl);

            $name = $document->find("div.partner-card-profile-header-title");
            $nameElements = isset($nameElements[0]) ? $nameElements[0]->text() : "Имя не найдено";

            $address = $document->find("a.simple-link");
            $addressElements = isset($addressElements[0]) ? $addressElements[0]->text() : "Адрес не найден";

            $this->parseProjects($partnerDocument);
        }
    }

    private function parseProjects(Document $partnerDocument)
    {
        $projectLinks = $partnerDocument->find('a.partner-project-pane__inner');

        foreach ($projectLinks as $projectLink) {
            $projectUrl = $projectLink->getAttribute('href');
            $fullProjectUrl = "https://www.1c-bitrix.ru" . $projectUrl;

            $projectDocument = $this->getDocument($fullProjectUrl);

            $details = $this->extractProjectDetails($projectDocument);

            $this->writeProjectInfo($details);

        }
    }

    private function getDocument($url)
    {
        while (true)
        {
            try {
                return new Document($url, true);
            }
            catch (Throwable) {
                echo "Ошибка подключение документа {$url}<br><br>";
                sleep(30);
            }
        }
    }

    private function getId ()
    {
        static $id = 1;
        return $id++;
    }

    private function extractProjectDetails(Document $document)
    {
        $details = [
            'site' => 'Сайт не найден',
            'redaction' => 'Редакция не найдена',
            'description' => 'Описание не найдено'
        ];

        $items = $document->find('ul.detail-page-list__list li');

        $redactionElement = $items[2]->find('div.detail-page-list__item-record_value');
        $details['redaction'] = isset($redactionElement[0]) ? trim($redactionElement[0]->text()) : $details['redaction'];
        
        $siteElement = $items[3]->find('div.detail-page-list__item-record_value');
        $details['site'] = isset($siteElement[0]) ? trim($siteElement[0]->text()) : $details['site'];
        
        $descriptionElement = $document->find('div.detail-page-case');
        $details['description'] = trim($descriptionElement[0]->text());

        return $details;
    }

    private function writeProjectInfo($details)
    {
        $outputLine = "{$this->getId()}, {$details['site']}, {$details['redaction']}, {$details['description']}\n\n";
        file_put_contents('projects.txt', $outputLine, FILE_APPEND);
    }

    public function parse()
    {
        set_time_limit(0);

        file_put_contents('projects.txt', "");

        while ($this->page <= $this->pageEnd) {
            $this->parsePage($this->page);
            $this->page++;
        }
    }
}
