package org.example.controllers;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.layout.VBox;
import org.example.Models.Cours;
import org.example.Services.ServiceCours;

import java.sql.Date;
import java.time.LocalDate;
import java.util.regex.Pattern;

public class CoursDialogController {

    @FXML private Label dialogTitle;
    @FXML private TextField titreField;
    @FXML private TextArea descriptionArea;
    @FXML private ComboBox<String> niveauCombo;
    @FXML private TextField matiereField;
    @FXML private TextField emailProfField;
    @FXML private TextField dureeField;
    @FXML private ComboBox<String> statutCombo;
    @FXML private Button saveButton;

    // Labels d'erreur
    @FXML private Label titreError;
    @FXML private Label descriptionError;
    @FXML private Label niveauError;
    @FXML private Label matiereError;
    @FXML private Label emailProfError;
    @FXML private Label dureeError;
    @FXML private Label statutError;

    private ServiceCours serviceCours;
    private Cours cours;
    private CoursController parentController;

    // Pattern pour valider l'email
    private static final Pattern EMAIL_PATTERN =
            Pattern.compile("^[A-Za-z0-9+_.-]+@(.+)$");

    @FXML
    public void initialize() {
        // Initialiser les ComboBox
        niveauCombo.setItems(javafx.collections.FXCollections.observableArrayList(
                "Débutant", "Intermédiaire", "Avancé", "Expert"
        ));

        statutCombo.setItems(javafx.collections.FXCollections.observableArrayList(
                "Brouillon", "Publié", "Archivé"
        ));

        // Ajouter des listeners pour la validation en temps réel
        setupValidationListeners();
    }

    private void setupValidationListeners() {
        titreField.textProperty().addListener((obs, old, newVal) -> validateTitre());
        descriptionArea.textProperty().addListener((obs, old, newVal) -> validateDescription());
        niveauCombo.valueProperty().addListener((obs, old, newVal) -> validateNiveau());
        matiereField.textProperty().addListener((obs, old, newVal) -> validateMatiere());
        emailProfField.textProperty().addListener((obs, old, newVal) -> validateEmailProf());
        dureeField.textProperty().addListener((obs, old, newVal) -> validateDuree());
        statutCombo.valueProperty().addListener((obs, old, newVal) -> validateStatut());
    }

    // Validation du titre
    private boolean validateTitre() {
        String titre = titreField.getText();
        if (titre == null || titre.trim().isEmpty()) {
            titreError.setText("Le titre est obligatoire");
            titreField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else if (titre.trim().length() < 3) {
            titreError.setText("Le titre doit contenir au moins 3 caractères");
            titreField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            titreError.setText("");
            titreField.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    // Validation de la description
    private boolean validateDescription() {
        String description = descriptionArea.getText();
        if (description == null || description.trim().isEmpty()) {
            descriptionError.setText("La description est obligatoire");
            descriptionArea.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else if (description.trim().length() < 10) {
            descriptionError.setText("La description doit contenir au moins 10 caractères");
            descriptionArea.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            descriptionError.setText("");
            descriptionArea.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    // Validation du niveau
    private boolean validateNiveau() {
        String niveau = niveauCombo.getValue();
        if (niveau == null || niveau.isEmpty()) {
            niveauError.setText("Veuillez sélectionner un niveau");
            niveauCombo.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            niveauError.setText("");
            niveauCombo.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    // Validation de la matière
    private boolean validateMatiere() {
        String matiere = matiereField.getText();
        if (matiere == null || matiere.trim().isEmpty()) {
            matiereError.setText("La matière est obligatoire");
            matiereField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            matiereError.setText("");
            matiereField.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    // Validation de l'email du professeur
    private boolean validateEmailProf() {
        String email = emailProfField.getText();
        if (email == null || email.trim().isEmpty()) {
            emailProfError.setText("L'email du professeur est obligatoire");
            emailProfField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else if (!EMAIL_PATTERN.matcher(email.trim()).matches()) {
            emailProfError.setText("Veuillez entrer un email valide (ex: nom@domaine.com)");
            emailProfField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            emailProfError.setText("");
            emailProfField.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    // Validation de la durée
    private boolean validateDuree() {
        String dureeText = dureeField.getText();
        if (dureeText == null || dureeText.trim().isEmpty()) {
            dureeError.setText("La durée est obligatoire");
            dureeField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        }

        try {
            double duree = Double.parseDouble(dureeText);
            if (duree <= 0) {
                dureeError.setText("La durée doit être un nombre positif (supérieur à 0)");
                dureeField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
                return false;
            } else if (duree > 1000) {
                dureeError.setText("La durée est trop élevée (maximum 1000 heures)");
                dureeField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
                return false;
            } else {
                dureeError.setText("");
                dureeField.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
                return true;
            }
        } catch (NumberFormatException e) {
            dureeError.setText("Veuillez entrer un nombre valide (ex: 10.5 ou 15)");
            dureeField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        }
    }

    // Validation du statut
    private boolean validateStatut() {
        String statut = statutCombo.getValue();
        if (statut == null || statut.isEmpty()) {
            statutError.setText("Veuillez sélectionner un statut");
            statutCombo.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            statutError.setText("");
            statutCombo.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    // Validation de tous les champs
    private boolean validateAllFields() {
        boolean isValid = true;
        isValid &= validateTitre();
        isValid &= validateDescription();
        isValid &= validateNiveau();
        isValid &= validateMatiere();
        isValid &= validateEmailProf();
        isValid &= validateDuree();
        isValid &= validateStatut();
        return isValid;
    }

    public void setServiceCours(ServiceCours serviceCours) {
        this.serviceCours = serviceCours;
    }

    public void setCours(Cours cours) {
        this.cours = cours;
        if (cours != null) {
            dialogTitle.setText("Modifier le cours");
            titreField.setText(cours.getTitre());
            descriptionArea.setText(cours.getDescription());
            niveauCombo.setValue(cours.getNiveau());
            matiereField.setText(cours.getMatiere());
            emailProfField.setText(cours.getEmail_prof());  // Utiliser getEmail_prof()
            dureeField.setText(String.valueOf(cours.getDuree()));
            statutCombo.setValue(cours.getStatut());
        }
    }

    public void setParentController(CoursController parentController) {
        this.parentController = parentController;
    }

    @FXML
    private void handleSauvegarder() {
        // Valider tous les champs avant sauvegarde
        if (!validateAllFields()) {
            showGlobalError();
            return;
        }

        if (cours == null) {
            cours = new Cours();
        }

        cours.setTitre(titreField.getText().trim());
        cours.setDescription(descriptionArea.getText().trim());
        cours.setNiveau(niveauCombo.getValue());
        cours.setMatiere(matiereField.getText().trim());
        cours.setEmail_prof(emailProfField.getText().trim());  // Utiliser setEmail_prof()
        cours.setDuree((int) Double.parseDouble(dureeField.getText()));
        cours.setStatut(statutCombo.getValue());
        cours.setDate_creation(Date.valueOf(LocalDate.now()));

        // Si user_id n'est pas défini, mettre une valeur par défaut (1 = admin)
        if (cours.getUser_id() == 0) {
            cours.setUser_id(1);
        }

        try {
            if (cours.getId() == 0) {
                serviceCours.ajouter(cours);
                showSuccessMessage("Cours ajouté avec succès !");
            } else {
                serviceCours.modifier(cours);
                showSuccessMessage("Cours modifié avec succès !");
            }
            parentController.refreshTable();
            fermerDialog();
        } catch (Exception e) {
            showErrorMessage("Erreur lors de l'opération: " + e.getMessage());
        }
    }

    private void showGlobalError() {
        Label errorLabel = new Label("Veuillez corriger les erreurs dans le formulaire");
        errorLabel.setStyle("-fx-text-fill: #f44336; -fx-font-size: 12px; -fx-padding: 10 0 0 0;");

        VBox parentVBox = (VBox) saveButton.getScene().getRoot();
        if (parentVBox != null && !parentVBox.getChildren().contains(errorLabel)) {
            parentVBox.getChildren().add(errorLabel);
            new Thread(() -> {
                try {
                    Thread.sleep(3000);
                    Platform.runLater(() -> parentVBox.getChildren().remove(errorLabel));
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
            }).start();
        }
    }

    private void showSuccessMessage(String message) {
        Label successLabel = new Label("✓ " + message);
        successLabel.setStyle("-fx-text-fill: #4CAF50; -fx-font-size: 12px; -fx-padding: 10 0 0 0;");

        VBox parentVBox = (VBox) saveButton.getScene().getRoot();
        if (parentVBox != null) {
            parentVBox.getChildren().add(successLabel);
            new Thread(() -> {
                try {
                    Thread.sleep(2000);
                    Platform.runLater(() -> parentVBox.getChildren().remove(successLabel));
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
            }).start();
        }
    }

    private void showErrorMessage(String message) {
        Label errorLabel = new Label("✗ " + message);
        errorLabel.setStyle("-fx-text-fill: #f44336; -fx-font-size: 12px; -fx-padding: 10 0 0 0;");

        VBox parentVBox = (VBox) saveButton.getScene().getRoot();
        if (parentVBox != null) {
            parentVBox.getChildren().add(errorLabel);
            new Thread(() -> {
                try {
                    Thread.sleep(3000);
                    Platform.runLater(() -> parentVBox.getChildren().remove(errorLabel));
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
            }).start();
        }
    }

    @FXML
    private void handleAnnuler() {
        fermerDialog();
    }

    private void fermerDialog() {
        saveButton.getScene().getWindow().hide();
    }
}