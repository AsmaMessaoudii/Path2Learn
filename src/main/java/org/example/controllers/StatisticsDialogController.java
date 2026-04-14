package org.example.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.ProgressBar;
import javafx.scene.control.Button;
import javafx.stage.Stage;

public class StatisticsDialogController {

    @FXML private Label totalStudentsLabel;
    @FXML private Label studentsWithPortfolioLabel;
    @FXML private Label studentsWithoutPortfolioLabel;
    @FXML private Label coveragePercentageLabel;
    @FXML private Label totalPortfoliosLabel;
    @FXML private Label averagePerStudentLabel;
    @FXML private Label infoMessageLabel;
    @FXML private ProgressBar coverageProgressBar;
    @FXML private Button closeButton;

    public void setStatistics(int totalStudents, int studentsWithPortfolio,
                              int studentsWithoutPortfolio, double coveragePercentage,
                              int totalPortfolios, double averagePerStudent) {

        totalStudentsLabel.setText(String.valueOf(totalStudents));
        studentsWithPortfolioLabel.setText(String.valueOf(studentsWithPortfolio));
        studentsWithoutPortfolioLabel.setText(String.valueOf(studentsWithoutPortfolio));
        coveragePercentageLabel.setText(String.format("%.1f%%", coveragePercentage));
        totalPortfoliosLabel.setText(String.valueOf(totalPortfolios));
        averagePerStudentLabel.setText(String.format("%.2f", averagePerStudent));

        // Update progress bar
        coverageProgressBar.setProgress(coveragePercentage / 100);

        // Set info message based on coverage
        String message;
        if (coveragePercentage >= 75) {
            message = "🏆 Excellent ! Plus de 75% des étudiants ont créé leur portfolio. Félicitations !";
        } else if (coveragePercentage >= 50) {
            message = "✅ Bonne progression ! Plus de la moitié des étudiants ont un portfolio.";
        } else if (coveragePercentage >= 25) {
            message = "📈 À améliorer ! Encouragez les étudiants à créer leur portfolio.";
        } else {
            message = "⚠️ Attention ! Très peu d'étudiants ont créé un portfolio. Une action est nécessaire.";
        }
        infoMessageLabel.setText(message);
    }

    @FXML
    private void handleFermer() {
        Stage stage = (Stage) closeButton.getScene().getWindow();
        stage.close();
    }
}