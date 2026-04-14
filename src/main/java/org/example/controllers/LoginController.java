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
import java.sql.SQLException;

public class LoginController {

    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;
    @FXML private Label errorLabel;

    private ServiceUser serviceUser = new ServiceUser();

    @FXML
    private void handleLogin() {

        String email = emailField.getText();
        String password = passwordField.getText();

        if (email.isBlank() || password.isBlank()) {
            errorLabel.setText("Email et mot de passe obligatoires");
            return;
        }

        try {
            User user = serviceUser.authentifier(email, password);

            if (user != null) {

                // OPEN HOME WITH USER
                FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/HomePage.fxml"));
                Parent root = loader.load();

                HomePageController controller = loader.getController();
                controller.setCurrentUser(user);

                Stage stage = (Stage) emailField.getScene().getWindow();
                stage.setScene(new Scene(root));
                stage.setTitle("Home - " + user.getNom());
                stage.show();

            } else {
                errorLabel.setText("Email ou mot de passe incorrect");
            }

        } catch (IOException | SQLException e) {
            e.printStackTrace();
        }
    }
    @FXML
    private void goHome() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/HomePage.fxml"));
            Parent root = loader.load();

            Stage stage = (Stage) emailField.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Home");
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            errorLabel.setText("Erreur retour home");
        }
    }

    @FXML
    private void goRegister() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/register.fxml"));
            Parent root = loader.load();

            Stage stage = (Stage) emailField.getScene().getWindow();
            stage.setScene(new Scene(root));

        } catch (IOException e) {
            e.printStackTrace();
        }
    }

}
