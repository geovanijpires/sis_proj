# Generated by Django 5.0.4 on 2024-04-04 01:26

import django.db.models.deletion
from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('api', '0007_apropriation'),
    ]

    operations = [
        migrations.RemoveField(
            model_name='apropriation',
            name='project_type',
        ),
        migrations.AddField(
            model_name='apropriation',
            name='project',
            field=models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.CASCADE, to='api.project'),
        ),
    ]
