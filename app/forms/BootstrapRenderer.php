<?php
use Nette\Forms\Controls;

/**
 * BoostrapRenderer allows Boostrap rendering of Nette\Forms
 *
 * @author Petr
 */
class BootstrapRenderer {

    /**
     * Sets boostrap rendering to passed form.
     * 
     * @param type $form form which will be boostrap rendered
     * @param type $ajax sets asynchronous processing of this form if true
     */
    public static function setBoostrapRendering(&$form, $ajax = false) {
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
        // make form and controls compatible with Twitter Bootstrap
        $formClasses = 'form-horizontal';
        if ($ajax) {
            $formClasses .= " ajax";
        }
        $form->getElementPrototype()->class($formClasses);
        foreach ($form->getControls() as $control) {
            if ($control instanceof Controls\Button) {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = TRUE;
            } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');
            } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }
    }

}
