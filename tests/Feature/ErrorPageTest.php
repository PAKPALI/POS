<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_unknown_page_uses_the_robot_error_experience(): void
    {
        config(['app.debug' => false]);

        $this->get('/adresse-qui-nexiste-pas')
            ->assertNotFound()
            ->assertSee('Cette page semble avoir disparu')
            ->assertSee('access-denied-robot.png');
    }

    public function test_common_error_views_render_a_contextual_robot_message(): void
    {
        foreach ([
            419 => 'Votre session de sécurité a expiré',
            429 => 'Prenons une courte pause',
            500 => 'Le robot fait une vérification technique',
            503 => 'Le robot améliore votre espace',
            405 => 'Cette action ne peut pas être effectuée ici',
        ] as $status => $message) {
            $this->view("errors.{$status}")
                ->assertSee($message)
                ->assertSee('access-denied-robot.png');
        }
    }
}
