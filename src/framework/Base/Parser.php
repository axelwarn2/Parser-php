<?php

namespace Framework;

include "framework/framework.php";

use DiDom\Document;

class Parser
{
    private $page;
    private $pageEnd;
    private $partnerId;
    private $projectId;

    public function __construct(int $page = 1, int $pageEnd = 120)
    {
        $this->page = $page;
        $this->pageEnd = $pageEnd;
        $this->partnerId = 1;
        $this->projectId = 1;
    }

    private function getDocument($url)
    {
        while (true)
        {
            try {
                return new Document($url, true);
            }
            catch (\Throwable) {
                echo "Ошибка подключение документа {$url}<br><br>";
                sleep(30);
            }
        }
    }

    protected function parsePartner(int $page): void
    {
        $document = $this->getDocument("https://www.1c-bitrix.ru/partners/index_ajax.php?PAGEN_1={$page}");

        foreach ($document->find('a.bx-ui-tile__main-link') as $el) {
            $relativePath = trim($el->getAttribute('href'), './');
            $partnerPageUrl = "https://www.1c-bitrix.ru/partners/" . $relativePath;

            $partnerDocument = $this->getDocument($partnerPageUrl);

            $name = $partnerDocument->find("div.partner-card-profile-header-title");
            $nameElements = isset($name[0]) ? $name[0]->text() : "Имя не найдено";
            
            $address = $partnerDocument->find("a.simple-link");
            $addressElements = isset($address[0]) ? $address[0]->text() : "Адрес не найден";

            $this->parseProjects($partnerDocument);

            $this->writePartner($nameElements, $partnerPageUrl, $addressElements);
            $this->partnerId++;
        }
    }

    private function parseProjects(Document $partnerDocument, int $partnerId): void
    {
        $projectLinks = $partnerDocument->find('a.partner-project-pane__inner');

        foreach ($projectLinks as $projectLink) {
            $projectUrl = $projectLink->getAttribute('href');
            $ProjectPathUrl = "https://www.1c-bitrix.ru" . $projectUrl;

            $projectDocument = $this->getDocument($ProjectPathUrl);

            $items = $projectDocument->find('ul.detail-page-list__list li');

            $siteProject = isset($items[3]) ? trim($items[3]->find('div.detail-page-list__item-record_value')[0]->text()) : 'Сайт не найден';

            $redactionProject = isset($items[2]) ? trim($items[2]->find('div.detail-page-list__item-record_value')[0]->text()) : 'Редакция не найдена';

            $descriptionProject = isset($projectDocument->find('div.detail-page-case')[0]) ? trim($projectDocument->find('div.detail-page-case')[0]->text()) : 'Описание не найдено';
            $descriptionProject = $this->formatText($descriptionProject);

            $this->writeProject($partnerId, $siteProject, $redactionProject, $descriptionProject);
            $this->projectId++;
        } 
    }

    private function formatText(string $text): string
    {
        $text = preg_replace('/\s*(\n|\s{2,}|\s+([,.!?]))/', $text);
        return $text;
    }

    private function writePartner(string $name, string $partnerPageUrl, string $address): void 
    {
        $outputLine = "{$this->partnerId}| {$name}| {$partnerPageUrl}| {$address}\n";
        file_put_contents('partners.txt', $outputLine, FILE_APPEND);
    }

    private function writeProject(int $partnerId, string $siteProject, string $redactionProject, string $descriptionProject): void
    {
        $outputLine = "{$partnerId}| http://{$siteProject}| {$redactionProject}| {$descriptionProject}\n";
        file_put_contents('projects.txt', $outputLine, FILE_APPEND);
    }

    public function parse(): void
    {
        set_time_limit(0);
        file_put_contents('partners.txt', "");

        for ($this->page; $this->page <= $this->pageEnd; $this->page++) {
            $this->parsePartner($this->page);
            sleep(1);
        }

        $this->parseFile();
    }

    private function parseFile(): void
    {
        $file = fopen('partners.txt', 'r');

        while (($line = fgets($file)) !== false) {
            $fields = explode('| ', $line);
            $partnerId = (int)$fields[0];
            $partnerPageUrl = $fields[2];

            if ($partnerId < 1884) {
                continue;
            }

            if (get_headers($partnerPageUrl)[0] === "HTTP/1.1 404 Not Found") {
                continue;
            }

            $partnerDocument = $this->getDocument($partnerPageUrl);
            $this->parseProjects($partnerDocument, $partnerId);
        }

        fclose($file);
    }
}
