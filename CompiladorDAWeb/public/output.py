import pandas as pd
import matplotlib.pyplot as plt
print('=== INICIANDO ANÁLISIS DE DATOS ===')
resultado = pd.read_csv("data_1762443033.csv")
print('Dataset cargado:', resultado.shape)
print('Columnas:', list(resultado.columns))
print('Primeras filas:')
print(resultado.head())

resultado = resultado[resultado['edad'] > 25]
print('Filtro aplicado. Nuevo shape:', resultado.shape)

resultado.to_csv("resultado.csv")

print('=== ANÁLISIS COMPLETADO ===')
print('DataFrame original:', resultado.shape)
if 'resultado' in locals():
    print('Resultados calculados:')
    print(resultado)
else:
    print('No se calcularon resultados agrupados')
