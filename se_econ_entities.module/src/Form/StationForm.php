<?php

namespace Drupal\se_econ_entities\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Language\Language;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the se_econ_entities entity edit forms.
 *
 * @ingroup se_econ_entities
 */
class StationForm extends ContentEntityForm {
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        /* @var $entity \Drupal\se_econ_entities\Entity\Station */
        $form = parent::buildForm($form, $form_state);
        $entity = $this->entity;
        
        $form['langcode'] = array(
            '#title' => $this->t('Language'),
            '#type' => 'language_select',
            '#default_value' => $entity->getUntranslated()->language()->getId(),
            '#languages' => Language::STATE_ALL,
        );
        return $form;
    }
    
    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state) {
        $status = parent::save($form, $form_state);
        
        $entity = $this->entity;
        if ($status == SAVED_UPDATED) {
            drupal_set_message($this->t('The station %feed has been updated.', ['%feed' => $entity->toLink()->toString()]));
        } else {
            drupal_set_message($this->t('The station %feed has been added.', ['%feed' => $entity->toLink()->toString()]));
        }
        
        $form_state->setRedirectUrl($this->entity->toUrl('collection'));
        return $status;
    }
}

?>