package org.example.validators;

import org.example.Models.Choix;
import java.util.ArrayList;
import java.util.List;

public class ChoixValidator {

    private List<String> errors;

    public ChoixValidator() {
        errors = new ArrayList<>();
    }

    public boolean validate(Choix choix) {
        errors.clear();

        // Validation du contenu
        if (choix.getContenu() == null || choix.getContenu().trim().isEmpty()) {
            errors.add("Le contenu de l'option est obligatoire");
        } else if (choix.getContenu().length() < 1) {
            errors.add("L'option doit contenir au moins 1 caractère");
        } else if (choix.getContenu().length() > 500) {
            errors.add("L'option ne doit pas dépasser 500 caractères");
        } else if (!choix.getContenu().matches("^[a-zA-Z0-9\\s\\p{L}À-ÿ\\-\\'\\?\\!\\,\\.]+$")) {
            errors.add("Le contenu contient des caractères non autorisés");
        }

        // Validation de l'ID question
        if (choix.getQuestionId() <= 0) {
            errors.add("L'ID de la question doit être valide");
        }

        // Validation de la correction (au moins une option correcte par question sera vérifiée ailleurs)
        // On ne bloque pas ici car on peut avoir plusieurs options incorrectes

        return errors.isEmpty();
    }

    public boolean validateForQuestion(List<Choix> choixList, int questionId) {
        errors.clear();

        if (choixList == null || choixList.isEmpty()) {
            errors.add("Une question doit avoir au moins une option de réponse");
            return false;
        }

        // Vérifier qu'il y a au moins une option correcte
        boolean hasCorrect = false;
        for (Choix choix : choixList) {
            if (choix.isEstCorrect()) {
                hasCorrect = true;
                break;
            }
        }

        if (!hasCorrect) {
            errors.add("La question doit avoir au moins une option correcte");
        }

        // Vérifier qu'il n'y a pas plusieurs options correctes (si vous voulez une seule bonne réponse)
        int correctCount = 0;
        for (Choix choix : choixList) {
            if (choix.isEstCorrect()) {
                correctCount++;
            }
        }

        if (correctCount > 1) {
            errors.add("Une seule option peut être marquée comme correcte");
        }

        return errors.isEmpty();
    }

    public List<String> getErrors() {
        return errors;
    }

    public String getErrorsAsString() {
        return String.join("\n• ", errors);
    }
}