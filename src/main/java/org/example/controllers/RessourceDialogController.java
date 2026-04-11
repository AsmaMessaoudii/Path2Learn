package org.example.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.layout.VBox;
import javafx.stage.FileChooser;
import org.example.Models.Cours;
import org.example.Models.RessourcePedagogique;
import org.example.Services.ServiceRessourcePedagogique;

import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.sql.Date;
import java.sql.Timestamp;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.List;
import java.util.UUID;

public class RessourceDialogController {

    @FXML private Label dialogTitle;
    @FXML private TextField titreField;
    @FXML private ComboBox<String> typeCombo;
    @FXML private TextField urlField;
    @FXML private TextField fileNameField;
    @FXML private ComboBox<String> coursCombo;
    @FXML private Button saveButton;
    @FXML private Button browseButton;

    // Labels d'erreur (à ajouter dans le FXML)
    @FXML private Label titreError;
    @FXML private Label typeError;
    @FXML private Label urlError;
    @FXML private Label coursError;

    private ServiceRessourcePedagogique serviceRessource;
    private List<Cours> coursList;
    private RessourcePedagogique ressource;
    private RessourcesController parentController;
    private File selectedFile;
    private static final String UPLOAD_DIR = "uploads";

    @FXML
    public void initialize() {
        // Créer le dossier d'uploads
        File uploadDir = new File(UPLOAD_DIR);
        if (!uploadDir.exists()) {
            uploadDir.mkdirs();
        }

        // Types de ressources
        typeCombo.setItems(javafx.collections.FXCollections.observableArrayList(
                "PDF", "Vidéo", "Lien externe", "Document", "Image", "Audio", "Autre"
        ));

        // Ajouter les listeners de validation
        setupValidationListeners();
    }

    private void setupValidationListeners() {
        titreField.textProperty().addListener((obs, old, newVal) -> validateTitre());
        typeCombo.valueProperty().addListener((obs, old, newVal) -> validateType());
        urlField.textProperty().addListener((obs, old, newVal) -> validateUrl());
        coursCombo.valueProperty().addListener((obs, old, newVal) -> validateCours());
    }

    private boolean validateTitre() {
        String titre = titreField.getText();
        if (titre == null || titre.trim().isEmpty()) {
            if (titreError != null) titreError.setText("Le titre est obligatoire");
            titreField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else if (titre.trim().length() < 3) {
            if (titreError != null) titreError.setText("Le titre doit contenir au moins 3 caractères");
            titreField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            if (titreError != null) titreError.setText("");
            titreField.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    private boolean validateType() {
        String type = typeCombo.getValue();
        if (type == null || type.isEmpty()) {
            if (typeError != null) typeError.setText("Veuillez sélectionner un type");
            typeCombo.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            if (typeError != null) typeError.setText("");
            typeCombo.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    private boolean validateUrl() {
        String url = urlField.getText();
        String fileName = fileNameField.getText();

        // Au moins un des deux doit être rempli
        if ((url == null || url.trim().isEmpty()) && (fileName == null || fileName.trim().isEmpty())) {
            if (urlError != null) urlError.setText("Veuillez saisir une URL ou sélectionner un fichier");
            urlField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        }

        // Si URL est remplie, la valider
        if (url != null && !url.trim().isEmpty()) {
            if (!url.trim().matches("^(http|https)://.*$")) {
                if (urlError != null) urlError.setText("L'URL doit commencer par http:// ou https://");
                urlField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
                return false;
            }
        }

        if (urlError != null) urlError.setText("");
        urlField.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
        return true;
    }

    private boolean validateCours() {
        String cours = coursCombo.getValue();
        if (cours == null || cours.isEmpty()) {
            if (coursError != null) coursError.setText("Veuillez sélectionner un cours");
            coursCombo.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            if (coursError != null) coursError.setText("");
            coursCombo.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    private boolean validateAllFields() {
        boolean isValid = true;
        isValid &= validateTitre();
        isValid &= validateType();
        isValid &= validateUrl();
        isValid &= validateCours();
        return isValid;
    }

    @FXML
    private void handleBrowseFile() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Sélectionner un fichier");

        FileChooser.ExtensionFilter allImages = new FileChooser.ExtensionFilter(
                "Images", "*.png", "*.jpg", "*.jpeg", "*.gif", "*.bmp", "*.svg", "*.webp");
        FileChooser.ExtensionFilter allVideos = new FileChooser.ExtensionFilter(
                "Vidéos", "*.mp4", "*.avi", "*.mov", "*.mkv", "*.wmv", "*.flv", "*.webm");
        FileChooser.ExtensionFilter allAudios = new FileChooser.ExtensionFilter(
                "Audios", "*.mp3", "*.wav", "*.flac", "*.ogg", "*.m4a", "*.aac");
        FileChooser.ExtensionFilter allPDFs = new FileChooser.ExtensionFilter("PDF", "*.pdf");
        FileChooser.ExtensionFilter allDocuments = new FileChooser.ExtensionFilter(
                "Documents", "*.doc", "*.docx", "*.xls", "*.xlsx", "*.ppt", "*.pptx", "*.txt", "*.rtf");
        FileChooser.ExtensionFilter allFiles = new FileChooser.ExtensionFilter("Tous les fichiers", "*.*");

        fileChooser.getExtensionFilters().addAll(
                allImages, allVideos, allAudios, allPDFs, allDocuments, allFiles);

        selectedFile = fileChooser.showOpenDialog(browseButton.getScene().getWindow());

        if (selectedFile != null) {
            fileNameField.setText(selectedFile.getName());

            if (titreField.getText().isEmpty()) {
                String titre = selectedFile.getName().replaceFirst("[.][^.]+$", "");
                titreField.setText(titre);
            }

            String extension = getFileExtension(selectedFile.getName());
            String detectedType = detectFileType(extension);
            if (detectedType != null) {
                typeCombo.setValue(detectedType);
            }

            // Revalider les champs
            validateAllFields();
        }
    }

    private String getFileExtension(String fileName) {
        int lastDot = fileName.lastIndexOf(".");
        if (lastDot > 0) {
            return fileName.substring(lastDot + 1).toLowerCase();
        }
        return "";
    }

    private String detectFileType(String extension) {
        switch (extension) {
            case "png": case "jpg": case "jpeg": case "gif": case "bmp": case "svg": case "webp":
                return "Image";
            case "mp4": case "avi": case "mov": case "mkv": case "wmv": case "flv": case "webm":
                return "Vidéo";
            case "mp3": case "wav": case "flac": case "ogg": case "m4a": case "aac":
                return "Audio";
            case "pdf":
                return "PDF";
            case "doc": case "docx": case "xls": case "xlsx": case "ppt": case "pptx": case "txt": case "rtf":
                return "Document";
            default:
                return null;
        }
    }

    private String copyFileToUploads(File sourceFile) throws IOException {
        String originalFileName = sourceFile.getName();
        String extension = getFileExtension(originalFileName);
        String uniqueFileName = UUID.randomUUID().toString() + "_" + System.currentTimeMillis() + "." + extension;

        Path destinationPath = Paths.get(UPLOAD_DIR, uniqueFileName);
        Files.copy(sourceFile.toPath(), destinationPath, StandardCopyOption.REPLACE_EXISTING);

        return destinationPath.toString();
    }

    public void setServiceRessource(ServiceRessourcePedagogique serviceRessource) {
        this.serviceRessource = serviceRessource;
    }

    public void setCoursList(List<Cours> coursList) {
        this.coursList = coursList;
        coursCombo.getItems().clear();
        for (Cours c : coursList) {
            coursCombo.getItems().add(c.getId() + " - " + c.getTitre());
        }
    }

    public void setRessource(RessourcePedagogique ressource) {
        this.ressource = ressource;
        if (ressource != null) {
            dialogTitle.setText("Modifier la ressource");
            titreField.setText(ressource.getTitre());
            typeCombo.setValue(ressource.getType());
            urlField.setText(ressource.getUrl());
            fileNameField.setText(ressource.getFile_name());

            String coursItem = ressource.getCours_id() + " - " +
                    coursList.stream()
                            .filter(c -> c.getId() == ressource.getCours_id())
                            .map(Cours::getTitre)
                            .findFirst()
                            .orElse("");
            coursCombo.setValue(coursItem);
        }
    }

    public void setParentController(RessourcesController parentController) {
        this.parentController = parentController;
    }

    @FXML
    private void handleSauvegarder() {
        if (!validateAllFields()) {
            showGlobalError();
            return;
        }

        if (ressource == null) {
            ressource = new RessourcePedagogique();
        }

        ressource.setTitre(titreField.getText().trim());
        ressource.setType(typeCombo.getValue());
        ressource.setUrl(urlField.getText());

        if (selectedFile != null) {
            try {
                String savedFilePath = copyFileToUploads(selectedFile);
                ressource.setFile_name(savedFilePath);
            } catch (IOException e) {
                showErrorMessage("Impossible de copier le fichier: " + e.getMessage());
                return;
            }
        } else if (ressource.getFile_name() == null) {
            ressource.setFile_name("");
        }

        String selectedCours = coursCombo.getValue();
        int coursId = Integer.parseInt(selectedCours.split(" - ")[0]);
        ressource.setCours_id(coursId);

        ressource.setDate_ajout(Date.valueOf(LocalDate.now()));
        ressource.setUpdated_at(Timestamp.valueOf(LocalDateTime.now()));

        try {
            if (ressource.getId() == 0) {
                serviceRessource.ajouter(ressource);
                showSuccessMessage("Ressource ajoutée avec succès !");
            } else {
                serviceRessource.modifier(ressource);
                showSuccessMessage("Ressource modifiée avec succès !");
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
                    javafx.application.Platform.runLater(() -> parentVBox.getChildren().remove(errorLabel));
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
                    javafx.application.Platform.runLater(() -> parentVBox.getChildren().remove(successLabel));
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
                    javafx.application.Platform.runLater(() -> parentVBox.getChildren().remove(errorLabel));
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