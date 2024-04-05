from django.contrib import admin
from .models import Project, ProjectType, Milestone, Risk, Apropriation

admin.site.register([Project,ProjectType,Milestone, Risk, Apropriation])

