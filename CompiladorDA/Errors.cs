using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace CompiladorDA
{
    public class Errors
    {
        public int count = 0;
        public System.IO.TextWriter errorStream = Console.Out;

        public void SemErr(string msg)
        {
            errorStream.WriteLine("ERROR: " + msg);
            count++;
        }

        public void SemErr(int line, int col, string msg)
        {
            errorStream.WriteLine($"{line}:{col} {msg}");
            count++;
        }

        public void Warning(string msg)
        {
            errorStream.WriteLine("WARNING: " + msg);
        }
    }
}
