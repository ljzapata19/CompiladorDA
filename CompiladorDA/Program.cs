using System;
using System.IO;
using System.Text;

namespace CompiladorDA
{
    class Program
    {
        static void Main(string[] args)
        {
            Console.OutputEncoding = Encoding.UTF8;

            try
            {
                string inputFile = "ejemplo.txt";

                // Si ejecutamos desde VS, el archivo puede estar en el directorio del proyecto
                if (!File.Exists(inputFile))
                {
                    // Buscar en el directorio del proyecto (no en bin/Debug)
                    string projectDir = Directory.GetParent(Directory.GetCurrentDirectory()).Parent.Parent.FullName;
                    inputFile = Path.Combine(projectDir, "ejemplo.txt");
                }

                if (!File.Exists(inputFile))
                {
                    Console.WriteLine("❌ Archivo 'ejemplo.txt' no encontrado.");
                    Console.WriteLine("📁 Buscado en: " + Path.GetFullPath(inputFile));
                    Console.WriteLine("💡 Asegúrate de que el archivo existe y tiene 'Copiar siempre' en propiedades.");
                    Console.WriteLine("\nPresiona cualquier tecla para salir...");
                    Console.ReadKey();
                    return;
                }

                string input = File.ReadAllText(inputFile);
                Console.WriteLine("=== COMPILANDO LENGUAJE NATURAL A PYTHON ===");
                Console.WriteLine($"📄 Entrada: {input}");
                Console.WriteLine();

                // ✅ CORRECTO: Pasar el CONTENIDO al Scanner
                Scanner scanner = new Scanner(new MemoryStream(Encoding.UTF8.GetBytes(input)));
                Parser parser = new Parser(scanner);

                parser.Parse();

                if (parser.errors.count == 0)
                {
                    Console.WriteLine("✅ Compilación exitosa!");
                    Console.WriteLine("📁 Archivo 'output.py' generado correctamente.");

                    // Mostrar el contenido generado
                    if (File.Exists("output.py"))
                    {
                        Console.WriteLine("\n=== CÓDIGO PYTHON GENERADO ===");
                        Console.WriteLine(File.ReadAllText("output.py"));

                        // Mostrar también la ubicación del archivo
                        string outputPath = Path.GetFullPath("output.py");
                        Console.WriteLine($"📂 Ubicación: {outputPath}");
                    }
                }
                else
                {
                    Console.WriteLine($"❌ Errores de compilación: {parser.errors.count}");
                }
            }
            catch (Exception e)
            {
                Console.WriteLine($"💥 Error: {e.Message}");
                Console.WriteLine(e.StackTrace);
            }

            Console.WriteLine("\nPresiona cualquier tecla para salir...");
            Console.ReadKey();
        }
    }
}