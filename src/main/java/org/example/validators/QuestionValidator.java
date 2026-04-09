package org.example.validators;

import org.example.Models.Question;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

public class QuestionValidator {

    private List<String> errors;

    public QuestionValidator() {
        errors = new ArrayList<>();
    }

    public boolean validate(Question question) {
        errors.clear();

        // Validation du titre
        if (question.getTitre() == null || question.getTitre().trim().isEmpty()) {
            errors.add("Le titre est obligatoire");
        } else if (question.getTitre().length() < 3) {
            errors.add("Le titre doit contenir au moins 3 caractères");
        } else if (question.getTitre().length() > 200) {
            errors.add("Le titre ne doit pas dépasser 200 caractères");
        } else if (!question.getTitre().matches("^[a-zA-Z0-9\\s\\p{L}À-ÿ\\-\\'\\?\\!\\,\\.]+$")) {
            errors.add("Le titre contient des caractères non autorisés");
        }

        // Validation de la description
        if (question.getDescription() == null || question.getDescription().trim().isEmpty()) {
            errors.add("La description est obligatoire");
        } else if (question.getDescription().length() < 10) {
            errors.add("La description doit contenir au moins 10 caractères");
        } else if (question.getDescription().length() > 1000) {
            errors.add("La description ne doit pas dépasser 1000 caractères");
        }

        // Validation de la durée
        if (question.getDuree() <= 0) {
            errors.add("La durée doit être supérieure à 0");
        } else if (question.getDuree() > 360) {
            errors.add("La durée ne peut pas dépasser 360 minutes (6 heures)");
        }

        // Validation de la note maximale
        if (question.getNoteMax() <= 0) {
            errors.add("La note maximale doit être supérieure à 0");
        } else if (question.getNoteMax() > 100) {
            errors.add("La note maximale ne peut pas dépasser 100");
        }

        // Validation de l'ID utilisateur
        if (question.getUserId() <= 0) {
            errors.add("L'ID utilisateur doit être valide");
        }

        // Validation de la date
        if (question.getDateCreation() == null) {
            errors.add("La date de création est obligatoire");
        } else {
            LocalDate creationDate = question.getDateCreation().toLocalDate();
            LocalDate today = LocalDate.now();
            if (creationDate.isAfter(today)) {
                errors.add("La date de création ne peut pas être dans le futur");
            }
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