<?php
namespace In2code\Powermail\ViewHelpers\Validation;

use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\Domain\Service\ConfigurationService;
use In2code\Powermail\Utility\LocalizationUtility;
use In2code\Powermail\Utility\ObjectUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Abstract Validation ViewHelper
 */
abstract class AbstractValidationViewHelper extends AbstractViewHelper
{

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * Configuration
     */
    protected $settings = [];

    /**
     * @var string
     */
    protected $extensionName;

    /**
     * Check if native validation is activated
     *
     * @return bool
     */
    protected function isNativeValidationEnabled()
    {
        return $this->settings['validation']['native'] === '1';
    }

    /**
     * Check if javascript validation is activated
     *
     * @return bool
     */
    protected function isClientValidationEnabled()
    {
        return $this->settings['validation']['client'] === '1';
    }

    /**
     * Set mandatory attributes
     *
     * @param array &$additionalAttributes
     * @param Field $field
     * @return void
     */
    protected function addMandatoryAttributes(array &$additionalAttributes, Field $field = null)
    {
        if ($field !== null && $field->isMandatory()) {
            if ($this->isNativeValidationEnabled()) {
                $additionalAttributes['required'] = 'required';
            } else {
                if ($this->isClientValidationEnabled()) {
                    $additionalAttributes['data-parsley-required'] = 'true';
                }
            }
            if ($this->isClientValidationEnabled()) {
                $additionalAttributes['data-parsley-required-message'] =
                    LocalizationUtility::translate('validationerror_mandatory');
                $additionalAttributes['data-parsley-trigger'] = 'change';

                /**
                 * Special case multiselect:
                 * Parsley sets the error messages after the wrapping div (but only for multiselect)
                 * So we define for this case where the errors should be included
                 */
                if ($field->getType() === 'select' && $field->isMultiselect()) {
                    $this->addErrorContainer($additionalAttributes, $field);
                }
            }
        }
    }

    /**
     * Define where to show errors in markup
     *
     * @param array $additionalAttributes
     * @param Field $field
     * @return array
     */
    protected function addErrorContainer(array &$additionalAttributes, Field $field)
    {
        $additionalAttributes['data-parsley-errors-container'] =
            '.powermail_field_error_container_' . $field->getMarker();
        return $additionalAttributes;
    }

    /**
     * Define where to set the error class in markup
     *
     * @param array $additionalAttributes
     * @param Field $field
     * @return array
     */
    protected function addClassHandler(array &$additionalAttributes, Field $field)
    {
        $additionalAttributes['data-parsley-class-handler'] =
            '.powermail_fieldwrap_' . $field->getMarker() . ' div:first > div';
        return $additionalAttributes;
    }

    /**
     * @return void
     */
    public function initialize()
    {
        $this->extensionName = $this->controllerContext->getRequest()->getControllerExtensionName();
        $this->contentObject = ObjectUtility::getContentObject();
        if ($this->arguments['extensionName'] !== null) {
            $this->extensionName = $this->arguments['extensionName'];
        }
        $configurationService = ObjectUtility::getObjectManager()->get(ConfigurationService::class);
        $this->settings = $configurationService->getTypoScriptSettings();
    }
}
