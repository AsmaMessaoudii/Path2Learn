package org.example.Models;

import java.util.Date;

public class Portfolio {
    private int id;
    private String titre;
    private String description;
    private Date dateCreation;
    private Date dateMiseAjour;
    private int userId;

    // Constructeur vide
    public Portfolio() {}

    // Constructeur SANS id (ajout)
    public Portfolio(String titre, String description, Date dateCreation, Date dateMiseAjour, int userId) {
        this.titre = titre;
        this.description = description;
        this.dateCreation = dateCreation;
        this.dateMiseAjour = dateMiseAjour;
        this.userId = userId;
    }

    // Constructeur AVEC id (modifier/supprimer)
    public Portfolio(int id, String titre, String description, Date dateCreation, Date dateMiseAjour, int userId) {
        this.id = id;
        this.titre = titre;
        this.description = description;
        this.dateCreation = dateCreation;
        this.dateMiseAjour = dateMiseAjour;
        this.userId = userId;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getTitre() { return titre; }
    public void setTitre(String titre) { this.titre = titre; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public Date getDateCreation() { return dateCreation; }
    public void setDateCreation(Date dateCreation) { this.dateCreation = dateCreation; }

    public Date getDateMiseAjour() { return dateMiseAjour; }
    public void setDateMiseAjour(Date dateMiseAjour) { this.dateMiseAjour = dateMiseAjour; }

    public int getUserId() { return userId; }
    public void setUserId(int userId) { this.userId = userId; }

    @Override
    public String toString() {
        return "Portfolio{" +
                "id=" + id +
                ", titre='" + titre + '\'' +
                ", description='" + description + '\'' +
                ", dateCreation=" + dateCreation +
                ", dateMiseAjour=" + dateMiseAjour +
                ", userId=" + userId +
                "}\n";
    }
}