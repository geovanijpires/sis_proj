from django.db import models
from django.contrib.auth.models import User

class ProjectType(models.Model):
    id = models.AutoField(primary_key=True)
    type = models.CharField(max_length=255)

    def __str__(self):
        return self.type


class Milestone(models.Model):
    id = models.AutoField(primary_key=True)
    description = models.CharField(max_length=255)
    expected_date = models.DateField()
    achieved_date = models.DateField(null=True, blank=True)
    status = models.CharField(max_length=255)

    def __str__(self):
        return f"{self.description} {self.status}"

class Risk(models.Model):
    id = models.AutoField(primary_key=True)
    description = models.CharField(max_length=255)

    def __str__(self):
        return f"{self.description}"


class Project(models.Model):
    id = models.AutoField(primary_key=True)
    cod_proj = models.CharField(max_length=255)
    name = models.CharField(max_length=255)
    length = models.FloatField()
    rooms = models.IntegerField()
    project_cost = models.FloatField()
    administrative_cost = models.FloatField()
    visits = models.IntegerField()
    project_type = models.ForeignKey(ProjectType, on_delete=models.CASCADE)
    milestone = models.ForeignKey(Milestone, on_delete=models.CASCADE,null=True, blank=True)
    risk = models.ForeignKey(Risk, on_delete=models.CASCADE,null=True, blank=True)

    def __str__(self):
        return f"{self.cod_proj} {self.name}"

class Apropriation(models.Model):
    id = models.AutoField(primary_key=True)
    date = models.DateField(null=True, blank=True)
    project = models.ForeignKey(Project, on_delete=models.CASCADE, null=True, blank=True)
    usuario = models.ForeignKey(User, on_delete=models.CASCADE, null=True, blank=True)
    total_hour = models.IntegerField(null=True, blank=True)

    def __str__(self):
        return f"Data de lancamento: {self.date} Projeto: {self.project} Usuário: {self.usuario} total de horas lançadas: {self.total_hour}"