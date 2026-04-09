package org.example.Models;

import java.sql.Timestamp;

public class Badge {
    private int id;
    private String name;
    private String description;
    private String icon;
    private int required_courses;
    private Timestamp created_at;

    public Badge() {}

    public Badge(String name, String description, String icon,
                 int required_courses, Timestamp created_at) {
        this.name = name;
        this.description = description;
        this.icon = icon;
        this.required_courses = required_courses;
        this.created_at = created_at;
    }

    public Badge(int id, String name, String description, String icon,
                 int required_courses, Timestamp created_at) {
        this.id = id;
        this.name = name;
        this.description = description;
        this.icon = icon;
        this.required_courses = required_courses;
        this.created_at = created_at;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }
    public String getName() { return name; }
    public void setName(String name) { this.name = name; }
    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }
    public String getIcon() { return icon; }
    public void setIcon(String icon) { this.icon = icon; }
    public int getRequired_courses() { return required_courses; }
    public void setRequired_courses(int required_courses) { this.required_courses = required_courses; }
    public Timestamp getCreated_at() { return created_at; }
    public void setCreated_at(Timestamp created_at) { this.created_at = created_at; }

    @Override
    public String toString() {
        return "Badge{id=" + id +
                ", name='" + name + "'" +
                ", description='" + description + "'" +
                ", icon='" + icon + "'" +
                ", required_courses=" + required_courses +
                ", created_at=" + created_at + "}";
    }
}