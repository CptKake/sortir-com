<?php

namespace App\Services;

use App\Entity\Participant;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageService
{
    private ParameterBagInterface $parameterBag;
    private SluggerInterface $slugger;

    public function __construct(ParameterBagInterface $parameterBag, SluggerInterface $slugger)
    {
        $this->parameterBag = $parameterBag;
        $this->slugger = $slugger;
    }

    public function uploadImage(Participant $participant, UploadedFile $file): void
    {
        $uploadDir = $this->parameterBag->get('kernel.project_dir') . '/public/uploads/photos/';

        // Créer le répertoire s'il n'existe pas
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Supprimer l'image actuelle sauf si c'est l'image par défaut
        if ($participant->getUrlPhoto() && $participant->getUrlPhoto() !== 'défaut.png') {
            $oldImagePath = $uploadDir . $participant->getUrlPhoto();
            if (file_exists($oldImagePath) && is_file($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        // Nettoyage du nom original
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = strtolower($participant->getPseudo()) . '-' . uniqid() . '.' . $file->guessExtension();

        // Déplacement du fichier
        $file->move(
            $uploadDir,
            $newFilename
        );

        // Mise à jour de l'entité
        $participant->setUrlPhoto($newFilename);
    }

    public function removeImage(Participant $participant): void
    {
        // On vérifie que l'utilisateur a une photo et que ce n'est pas déjà l'image par défaut
        if (!$participant->getUrlPhoto() || $participant->getUrlPhoto() === 'défaut.png') {
            return;
        }

        // Chemin du fichier à supprimer
        $uploadDir = $this->parameterBag->get('kernel.project_dir') . '/public/uploads/photos/';
        $imagePath = $uploadDir . $participant->getUrlPhoto();

        // Suppression physique du fichier
        if (file_exists($imagePath) && is_file($imagePath)) {
            unlink($imagePath);
        }

        // Mise à jour de l'entité
        $participant->setUrlPhoto('défaut.png');
    }

    public function ensureDefaultImageExists(): void
    {
        $uploadDir = $this->parameterBag->get('kernel.project_dir') . '/public/uploads/photos/';
        $defaultImagePath = $uploadDir . 'défaut.png';

        // Création du dossier si nécessaire
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Si l'image par défaut n'existe pas dans le dossier uploads, la copier depuis le dossier images
        if (!file_exists($defaultImagePath)) {
            $sourceImagePath = $this->parameterBag->get('kernel.project_dir') . '/public/images/défaut.png';

            if (file_exists($sourceImagePath)) {
                copy($sourceImagePath, $defaultImagePath);
            }
        }
    }
}