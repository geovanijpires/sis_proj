# Generated by Django 5.0.4 on 2024-04-03 17:53

from django.db import migrations


class Migration(migrations.Migration):

    dependencies = [
        ('api', '0001_initial'),
    ]

    operations = [
        migrations.RenameField(
            model_name='project',
            old_name='id_project_type',
            new_name='project_type',
        ),
    ]