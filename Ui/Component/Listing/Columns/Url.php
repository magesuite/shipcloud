<?php

namespace MageSuite\Shipcloud\Ui\Component\Listing\Columns;

class Url extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$fieldName] = $this->renderColumnHtml($item[$fieldName]);
        }

        return $dataSource;
    }

    protected function renderColumnHtml($url)
    {
        return sprintf(
            '<a href="%s">%s</a>',
            $url,
            __('View')
        );
    }
}
