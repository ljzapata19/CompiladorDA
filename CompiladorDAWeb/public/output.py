import pandas as pd
import matplotlib.pyplot as plt
print('=== INICIANDO ANÁLISIS DE DATOS ===')
resultado = pd.read_csv("datos.csv")
print('Dataset cargado:', resultado.shape)
print('Columnas:', list(resultado.columns))
print('Primeras filas:')
print(resultado.head())

resultado = resultado[resultado['provincia'] == "Mendoza"]
print('Filtro aplicado. Nuevo shape:', resultado.shape)

resultado = resultado.groupby(['mes'])

resultado = resultado.agg({'toneladas': 'sum'})

resultado = resultado.reset_index()
resultado.plot(kind='line', x='mes', y='toneladas')
plt.title('Resultados del Análisis')
plt.xlabel('mes')
plt.ylabel('toneladas')
plt.tight_layout()
plt.savefig('grafico.png')
print('Gráfico guardado como grafico.png')

resultado.to_csv("produccion_mendoza.csv")

# Guardar resultado automáticamente
resultado.to_csv('resultado.csv')
print('Resultados guardados automáticamente en resultado.csv')
print('=== ANÁLISIS COMPLETADO ===')
print('DataFrame original:', resultado.shape)
if 'resultado' in locals():
    print('Resultados calculados:')
    print(resultado)
else:
    print('No se calcularon resultados agrupados')
