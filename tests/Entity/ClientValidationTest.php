<?php

namespace App\Tests\Entity;

use App\Entity\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Règles de validation du formulaire d'inscription (exigences EX-01 à EX-04).
 * Chaque cas reprend une partition ou une valeur limite du catalogue de la semaine 4.
 * (L'unicité de l'e-mail, qui nécessite la base de données, relève des tests d'intégration.)
 */
class ClientValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
    }

    /** @return string[] messages d'erreur obtenus pour un champ */
    private function erreurs(string $champ, mixed $valeur): array
    {
        $client = new Client();
        $client->{'set' . ucfirst($champ)}($valeur);

        $messages = [];
        foreach ($this->validator->validateProperty($client, $champ) as $violation) {
            $messages[] = $violation->getMessage();
        }

        return $messages;
    }

    #[DataProvider('valeursValides')]
    public function testValeurValideAcceptee(string $champ, mixed $valeur): void
    {
        $this->assertSame([], $this->erreurs($champ, $valeur));
    }

    public static function valeursValides(): array
    {
        return [
            'CT-01 nom valide' => ['nom', 'Ghammam'],
            'CT-03 nom de 50 caractères (limite)' => ['nom', str_repeat('a', 50)],
            'CT-01 e-mail valide' => ['email', 'ines@test.tn'],
            'CT-01 CIN de 8 chiffres' => ['cin', '12345678'],
            'CT-10 téléphone vide (facultatif)' => ['telephone', null],
            'téléphone de 8 chiffres' => ['telephone', '98123456'],
            'adresse valide' => ['adresse', 'Tunis'],
        ];
    }

    #[DataProvider('valeursInvalides')]
    public function testValeurInvalideRefuseeAvecLeBonMessage(string $champ, mixed $valeur, string $message): void
    {
        $this->assertContains($message, $this->erreurs($champ, $valeur));
    }

    public static function valeursInvalides(): array
    {
        $cin = 'Le CIN doit contenir exactement 8 chiffres.';
        $tel = 'Le numéro de téléphone doit contenir exactement 8 chiffres.';

        return [
            'CT-02 nom vide' => ['nom', null, 'Le nom est obligatoire.'],
            'CT-02 nom composé d\'espaces' => ['nom', '   ', 'Le nom est obligatoire.'],
            'CT-04 nom de 51 caractères (limite)' => ['nom', str_repeat('a', 51), 'Le nom ne doit pas dépasser 50 caractères.'],
            'prénom vide' => ['prenom', null, 'Le prénom est obligatoire.'],
            'CT-05 e-mail sans @' => ['email', 'abc', 'L\'adresse e-mail "abc" n\'est pas valide.'],
            'e-mail vide' => ['email', null, 'L\'adresse e-mail est obligatoire.'],
            'CT-07 CIN de 7 chiffres (limite)' => ['cin', '1234567', $cin],
            'CT-08 CIN de 9 chiffres (limite)' => ['cin', '123456789', $cin],
            'CT-09 CIN avec lettres' => ['cin', '12AB5678', $cin],
            'CIN vide' => ['cin', null, 'Le CIN est obligatoire.'],
            'téléphone de 7 chiffres (limite)' => ['telephone', '9812345', $tel],
            'CT-11 téléphone de 9 chiffres (limite)' => ['telephone', '981234567', $tel],
            'téléphone avec lettres' => ['telephone', 'zzz', $tel],
            'adresse vide' => ['adresse', null, 'L\'adresse est obligatoire.'],
        ];
    }
}
