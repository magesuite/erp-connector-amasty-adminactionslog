<?php

namespace MageSuite\ErpConnectorAmastyAdminActionsLog\Logging\AdminActionsLog\Entity\SaveHandler;

class Connector extends \Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common
{
    const SENSITIVE_FIELD_PLACEHOLDER = 'sensitive_field_placeholder';

    /**
     * @var \MageSuite\ErpConnector\Model\ConnectorResolver
     */
    protected $connectorResolver;

    /**
     * @var null|string[];
     */
    protected $sensitiveFields = null;

    public function __construct(
        \Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter\ScalarValueFilter $scalarValueFilter,
        \Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter\KeyFilter $keyFilter,
        \MageSuite\ErpConnector\Model\ConnectorResolver $connectorResolver
    ) {
        parent::__construct($scalarValueFilter, $keyFilter);

        $this->connectorResolver = $connectorResolver;
    }

    protected function filterObjectData(array $data): array
    {
        $data = parent::filterObjectData($data);

        $sensitiveFields = $this->getSensitiveFields();

        if (empty($sensitiveFields)) {
            return $data;
        }

        foreach ($sensitiveFields as $sensitiveField) {
            if (!isset($data[$sensitiveField]) || empty($data[$sensitiveField])) {
                continue;
            }

            $data[$sensitiveField] = self::SENSITIVE_FIELD_PLACEHOLDER;
        }

        return $data;
    }

    protected function getSensitiveFields()
    {
        if ($this->sensitiveFields !== null) {
            return $this->sensitiveFields;
        }

        $sensitiveFields = [];

        $connectorConfigurationFields = $this->connectorResolver->getConnectorConfigurationFields();

        foreach ($connectorConfigurationFields as $connectorFields) {
            foreach ($connectorFields as $key => $field) {
                $modifierClass = $field['modifier_class'] ?? null;

                if ($modifierClass instanceof \MageSuite\ErpConnector\Model\Modifier\SensitiveModifier) {
                    $sensitiveFields[] = $key;
                }
            }
        }

        $this->sensitiveFields = array_unique($sensitiveFields);
        return $this->sensitiveFields;
    }
}
