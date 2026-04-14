package org.example.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;
import org.example.Models.User;
import org.example.Services.ServiceUser;

import java.io.IOException;
import java.sql.Timestamp;

public class RegisterController {

    @FXML private TextField nomField, prenomField, emailField;
    @FXML private ComboBox<String> roleCombo;
    @FXML private Label errorLabel;

    private ServiceUser serviceUser = new ServiceUser();

    @FXML
    public void initialize() {
        roleCombo.getItems().addAll("STUDENT", "TEACHER");
    }

    @FXML
    private void handleSubmit() {

        String nom = nomField.getText();
        String prenom = prenomField.getText();
        String email = emailField.getText();
        String password = passwordField.getText();
        String confirmPassword = confirmPasswordField.getText();
        String role = roleCombo.getValue();

        StringBuilder errors = new StringBuilder();

        // VALIDATION
        if (nom == null || nom.isEmpty()) errors.append("Nom obligatoire\n");
        if (prenom == null || prenom.isEmpty()) errors.append("Prénom obligatoire\n");

        if (email == null || email.isEmpty())
            errors.append("Email obligatoire\n");
        else if (!email.matches("^\\S+@\\S+\\.\\S+$"))
            errors.append("Email invalide\n");

        if (password == null || password.length() < 6)
            errors.append("Mot de passe ≥ 6 caractères\n");

        if (!password.equals(confirmPassword))
            errors.append("Les mots de passe ne correspondent pas\n");

        if (role == null)
            errors.append("Choisir un rôle\n");

        if (errors.length() > 0) {
            errorLabel.setText(errors.toString());
            return;
        }

        try {
            User user = new User();
            user.setNom(nom);
            user.setPrenom(prenom);
            user.setEmail(email);
            user.setRole(role);
            user.setStatus("ENABLE");
            user.setMot_de_passe(password);
            user.setDate_creation(new Timestamp(System.currentTimeMillis()));

            serviceUser.ajouter(user);

            // SUCCESS → HOME
            goToHome();

        } catch (Exception e) {
            e.printStackTrace();
            errorLabel.setText("Erreur lors de l'inscription");
        }
    }
    @FXML
    private void handleBack() {
        goToHome();
    }

    private void goToHome() {
        try {
            Parent root = FXMLLoader.load(getClass().getResource("/fxml/HomePage.fxml"));
            Stage stage = (Stage) nomField.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Accueil");
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
    @FXML private PasswordField passwordField;
    @FXML private PasswordField confirmPasswordField;
}