<?php
/**
 * Copyright (C) 2015 Esendex Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the Esendex Community License v1.0 as published by
 * the Esendex Ltd.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * Esendex Community Licence v1.0 for more details.
 *
 * You should have received a copy of the Esendex Community Licence v1.0
 * along with this program.  If not, see <http://www.esendex.com/esendexcommunitylicence/>
 */

/**
 * Class Esendex_Sms_Model_Trigger
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
final class Esendex_Sms_Model_Trigger extends Esendex_Sms_Model_TriggerAbstract
{
    /**
     * Delete trigger using correct model
     *
     * @return self
     */
    public function delete()
    {
        $triggerId  = $this->getId();
        $eventId    = $this->getResource()->getEventIdByTriggerId($triggerId);
        $trigger    = $this->getResource()->getTriggerModel($eventId);

        $trigger->setId($triggerId);
        if ($trigger instanceof Esendex_Sms_Model_Trigger) {
            parent::delete();
        } else {
            $trigger->delete();
        }

        return $this;
    }
}
