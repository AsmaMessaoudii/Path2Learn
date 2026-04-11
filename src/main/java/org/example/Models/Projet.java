package org.example.Models;

import java.util.Date;

public class Projet {
    private int id;
    private String titreProjet;
    private String text;
    private String description;
    private String technologies;
    private Date dateRealisation;
    private String lienDemo;
    private int portfolioId;

    // Constructeur vide
    public Projet() {}

    // Constructeur SANS id (ajout)
    public Projet(String titreProjet, String text, String description, String technologies, Date dateRealisation, String lienDemo, int portfolioId) {
        this.titreProjet = titreProjet;
        this.text = text;
        this.description = description;
        this.technologies = technologies;
        this.dateRealisation = dateRealisation;
        this.lienDemo = lienDemo;
        this.portfolioId = portfolioId;
    }

    // Constructeur AVEC id (modifier/supprimer)
    public Projet(int id, String titreProjet, String text, String description, String technologies, Date dateRealisation, String lienDemo, int portfolioId) {
        this.id = id;
        this.titreProjet = titreProjet;
        this.text = text;
        this.description = description;
        this.technologies = technologies;
        this.dateRealisation = dateRealisation;
        this.lienDemo = lienDemo;
        this.portfolioId = portfolioId;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getTitreProjet() { return titreProjet; }
    public void setTitreProjet(String titreProjet) { this.titreProjet = titreProjet; }

    public String getText() { return text; }
    public void setText(String text) { this.text = text; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public String getTechnologies() { return technologies; }
    public void setTechnologies(String technologies) { this.technologies = technologies; }

    public Date getDateRealisation() { return dateRealisation; }
    public void setDateRealisation(Date dateRealisation) { this.dateRealisation = dateRealisation; }

    public String getLienDemo() { return lienDemo; }
    public void setLienDemo(String lienDemo) { this.lienDemo = lienDemo; }

    public int getPortfolioId() { return portfolioId; }
    public void setPortfolioId(int portfolioId) { this.portfolioId = portfolioId; }

    @Override
    public String toString() {
        return "Projet{" +
                "id=" + id +
                ", titreProjet='" + titreProjet + '\'' +
                ", text='" + text + '\'' +
                ", description='" + description + '\'' +
                ", technologies='" + technologies + '\'' +
                ", dateRealisation=" + dateRealisation +
                ", lienDemo='" + lienDemo + '\'' +
                ", portfolioId=" + portfolioId +
                "}\n";
    }
}
